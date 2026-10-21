ARG PHP_IMAGE=php:8.3.36-fpm-bookworm@sha256:53cec4b54c747a101329e475ae86d5f7421bc5783a2868ceec07f871ee1a959a
FROM ${PHP_IMAGE} AS php-base
RUN php -r 'if (PHP_VERSION !== "8.3.36") { fwrite(STDERR, "PHP 8.3.36 required.\n"); exit(1); }'
COPY --from=mlocati/php-extension-installer@sha256:ac4b252c153a9f6de5578ff2c417790aba836bb49855543144606033eceda253 /usr/bin/install-php-extensions /usr/local/bin/
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    install-php-extensions calendar exif intl mysqli zip \
        apcu-5.1.28 luasandbox-4.1.3 redis-6.3.0 wikidiff2-1.14.2 imagick-3.8.1

FROM php-base AS runtime
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    set -eux; apt-get update; apt-get upgrade -y; \
    apt-get install -y --no-install-recommends openssh-client nano rsync s3cmd unzip imagemagick git ffmpeg python3 libvips-tools
COPY config/php-config.ini config/opcache.ini /usr/local/etc/php/conf.d/
RUN set -eux; \
    printf 'memory_limit = 256M\nmax_execution_time = 120\nmax_input_vars = 3000\n' > /usr/local/etc/php/conf.d/docker-php-capacity.ini; \
    printf '\npm = dynamic\npm.max_children = 32\npm.start_servers = 8\npm.min_spare_servers = 4\npm.max_spare_servers = 16\npm.max_requests = 1000\npm.process_idle_timeout = 60s\nrequest_terminate_timeout = 120s\n' >> /usr/local/etc/php-fpm.d/zz-docker.conf; \
    mkdir -p /var/www/mediawiki /usr/local/smw; chown www-data:www-data /var/www/mediawiki /usr/local/smw; \
    php-fpm -t; php -r 'if (ini_get("session.upload_progress.enabled")) { exit(1); }'

FROM runtime AS source
ARG MEDIAWIKI_MAJOR_VERSION=1.43
ARG MEDIAWIKI_VERSION=1.43.11
COPY --from=composer:2.10.3@sha256:d0c32820c62cc860fddd22adceed4eeda7698b093a7a88d503414172ce400626 /usr/bin/composer /usr/local/bin/composer
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
    --mount=type=cache,target=/var/lib/apt,sharing=locked \
    set -eux; test "$MEDIAWIKI_MAJOR_VERSION" = 1.43; test "$MEDIAWIKI_VERSION" = 1.43.11; \
    apt-get update; apt-get install -y --no-install-recommends gnupg dirmngr python3-pip; \
    curl -fSL "https://releases.wikimedia.org/mediawiki/${MEDIAWIKI_MAJOR_VERSION}/mediawiki-${MEDIAWIKI_VERSION}.tar.gz" -o /tmp/mediawiki.tar.gz; \
    curl -fSL "https://releases.wikimedia.org/mediawiki/${MEDIAWIKI_MAJOR_VERSION}/mediawiki-${MEDIAWIKI_VERSION}.tar.gz.sig" -o /tmp/mediawiki.tar.gz.sig; \
    export GNUPGHOME="$(mktemp -d)"; \
    gpg --batch --keyserver keyserver.ubuntu.com --recv-keys \
        D7D6767D135A514BEB86E9BA75682B08E8A3FEC4 441276E9CCD15F44F6D97D18C119E1A64D70938E \
        F7F780D82EBFB8A56556E7EE82403E59F9F8CD79 1D98867E82982C8FE0ABC25F9B69B3109D3BB7B0 \
        E059C034E7A430583C252F4AA8F734246D73B586; \
    gpg --batch --verify /tmp/mediawiki.tar.gz.sig /tmp/mediawiki.tar.gz; \
    tar -xzf /tmp/mediawiki.tar.gz --strip-components=1 -C /var/www/mediawiki; \
    gpgconf --kill all; rm -r "$GNUPGHOME" /tmp/mediawiki.tar.gz /tmp/mediawiki.tar.gz.sig; \
    pip3 install --break-system-packages --no-cache-dir Pygments==2.21.0; \
    chown -R www-data:www-data /var/www/mediawiki
WORKDIR /var/www/mediawiki

FROM source AS builder
COPY --chown=www-data:www-data build/composer.json build/composer.lock ./
COPY scripts/prepare-sesp.php /tmp/prepare-sesp.php
USER www-data
RUN --mount=type=cache,target=/var/www/.composer/cache,uid=33,gid=33 \
    set -eux; rm -rf /var/www/mediawiki/vendor; \
    composer validate --no-check-publish; \
    composer install --no-dev --prefer-dist --no-interaction --no-scripts; \
    composer check-platform-reqs --no-dev; php /tmp/prepare-sesp.php
RUN export PY_PACKAGES_PATH=$(python3 -c 'import sysconfig; print(sysconfig.get_path("platlib"))'); \
    tar -czf /tmp/python-packages.tar.gz -C "$PY_PACKAGES_PATH" .

FROM runtime AS final
WORKDIR /var/www/mediawiki
COPY --from=source /usr/local/bin/composer /usr/local/bin/composer
COPY --from=builder --chown=www-data:www-data /var/www/mediawiki /var/www/mediawiki
COPY --from=builder /tmp/python-packages.tar.gz /tmp/python-packages.tar.gz
COPY --from=builder /usr/local/bin/pygmentize /usr/local/bin/pygmentize
RUN set -eux; export PY_PACKAGES_PATH=$(python3 -c 'import sysconfig; print(sysconfig.get_path("platlib"))'); \
    mkdir -p "$PY_PACKAGES_PATH"; tar -xzf /tmp/python-packages.tar.gz -C "$PY_PACKAGES_PATH"; rm /tmp/python-packages.tar.gz
COPY --chown=www-data:www-data config/LocalSettings.php ./LocalSettings.php
COPY --chown=www-data:www-data compat ./recovery
COPY --chown=www-data:www-data resources ./resources
COPY --chown=www-data:www-data config/robots.txt ./robots.txt
USER www-data
CMD ["php-fpm"]
