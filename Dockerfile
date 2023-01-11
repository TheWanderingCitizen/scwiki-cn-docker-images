FROM php:7.4-fpm

# Version
ENV MEDIAWIKI_MAJOR_VERSION 1.39
ENV MEDIAWIKI_VERSION 1.39.1

# System dependencies
RUN set -eux; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		git \
		librsvg2-bin \
		imagemagick \
		ffmpeg \
		webp \
		unzip \
		openssh-client \
		# Required for SyntaxHighlighting
		python3 \
		python3-pygments \
		rsync \
		nano \
	; \
	rm -rf /var/lib/apt/lists/*

# Install the PHP extensions we need
RUN set -eux; \
	\
	savedAptMark="$(apt-mark showmanual)"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		libicu-dev \
		libonig-dev \
		libcurl4-gnutls-dev \
		libmagickwand-dev \
		libwebp6 \
		libzip-dev \
		liblua5.1-0-dev \
	; \
	\
	docker-php-ext-install -j "$(nproc)" \
		calendar \
		intl \
		mbstring \
		mysqli \
		opcache \
	; \
	\
	pecl install \ 
		APCu-5.1.21 \
		luasandbox \
		imagick \
		redis \
	; \
	docker-php-ext-enable \
		apcu \
		luasandbox \
		imagick  \
		redis \
	; \
	rm -r /tmp/pear; \
	\
	# reset apt-mark's "manual" list so that "purge --auto-remove" will remove all build dependencies
	apt-mark auto '.*' > /dev/null; \
	apt-mark manual $savedAptMark; \
	ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
		| awk '/=>/ { print $3 }' \
		| sort -u \
		| xargs -r dpkg-query -S \
		| cut -d: -f1 \
		| sort -u \
		| xargs -rt apt-mark manual; \
	\
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	rm -rf /var/lib/apt/lists/*


# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=60'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# MediaWiki setup
RUN set -eux; \
    fetchDeps=" \
        gnupg \
        dirmngr \
    "; \
    apt-get update; \
    apt-get install -y --no-install-recommends $fetchDeps; \
    \
    curl -fSL "https://releases.wikimedia.org/mediawiki/${MEDIAWIKI_MAJOR_VERSION}/mediawiki-${MEDIAWIKI_VERSION}.tar.gz" -o mediawiki.tar.gz; \
    curl -fSL "https://releases.wikimedia.org/mediawiki/${MEDIAWIKI_MAJOR_VERSION}/mediawiki-${MEDIAWIKI_VERSION}.tar.gz.sig" -o mediawiki.tar.gz.sig; \
    export GNUPGHOME="$(mktemp -d)"; \
    # gpg key from https://www.mediawiki.org/keys/keys.txt
    gpg --batch --keyserver keyserver.ubuntu.com --recv-keys \
        D7D6767D135A514BEB86E9BA75682B08E8A3FEC4 \
        441276E9CCD15F44F6D97D18C119E1A64D70938E \
        F7F780D82EBFB8A56556E7EE82403E59F9F8CD79 \
        1D98867E82982C8FE0ABC25F9B69B3109D3BB7B0 \
    ; \
    gpg --batch --verify mediawiki.tar.gz.sig mediawiki.tar.gz; \
	mkdir /var/www/provisioning; \
	mkdir /var/www/mediawiki; \
    tar -x --strip-components=1 -f mediawiki.tar.gz -C /var/www/provisioning; \
    gpgconf --kill all; \
    rm -r "$GNUPGHOME" mediawiki.tar.gz.sig mediawiki.tar.gz; \
    \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false $fetchDeps; \
    rm -rf /var/lib/apt/lists/*
    
COPY ./config/LocalSettings.php /var/www/provisioning/LocalSettings.php
COPY ./resources /var/www/provisioning/resources

COPY ./config/php-config.ini /usr/local/etc/php/conf.d/php-config.ini
COPY ./config/robots.txt /var/www/provisioning/robots.txt
COPY ./resources/assets/favicon.ico /var/www/provisioning/favicon.ico

RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini; \
    echo 'max_execution_time = 60' >> /usr/local/etc/php/conf.d/docker-php-executiontime.ini; 

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY composer.local.json /var/www/provisioning

RUN set -eux; \
   chown -R www-data:www-data /var/www

WORKDIR /var/www/provisioning

USER www-data

RUN set -eux; \
   /usr/bin/composer config --no-plugins allow-plugins.composer/installers true; \
   /usr/bin/composer install --no-dev \
     --ignore-platform-reqs \
     --no-ansi \
     --no-interaction \
     --no-scripts; \
   rm -f composer.lock.json ;\
   /usr/bin/composer update --no-dev \
                            --no-ansi \
                            --no-interaction \
                            --no-scripts; \
	\
	mv /var/www/provisioning/extensions/Checkuser /var/www/provisioning/extensions/CheckUser; \
	mv /var/www/provisioning/extensions/Dismissablesitenotice /var/www/provisioning/extensions/DismissableSiteNotice; \
	mv /var/www/provisioning/extensions/Externaldata /var/www/provisioning/extensions/ExternalData; \
	mv /var/www/provisioning/extensions/Nativesvghandler /var/www/provisioning/extensions/NativeSvgHandler; \
	mv /var/www/provisioning/extensions/Revisionslider /var/www/provisioning/extensions/RevisionSlider; \
	mv /var/www/provisioning/extensions/Rss /var/www/provisioning/extensions/RSS; \
	mv /var/www/provisioning/extensions/Shortdescription /var/www/provisioning/extensions/ShortDescription; \
	mv /var/www/provisioning/extensions/WikiSeo /var/www/provisioning/extensions/WikiSEO; \
	mv /var/www/provisioning/skins/citizen /var/www/provisioning/skins/Citizen; \
	chown -R www-data:www-data /var/www

WORKDIR /var/www/mediawiki

CMD ["php-fpm"]
