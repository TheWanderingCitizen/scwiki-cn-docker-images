FROM php:8.1-fpm

# Version
ENV MEDIAWIKI_MAJOR_VERSION 1.39
ENV MEDIAWIKI_VERSION 1.39.4

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
  		liblua5.1-0 \
  		libzip4 \
        	s3cmd \
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
		libwebp7 \
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
  		zip \
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
	mkdir /var/www/mediawiki; \
    tar -x --strip-components=1 -f mediawiki.tar.gz -C /var/www/mediawiki; \
    gpgconf --kill all; \
    rm -r "$GNUPGHOME" mediawiki.tar.gz.sig mediawiki.tar.gz; \
    \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false $fetchDeps; \
    rm -rf /var/lib/apt/lists/*
    
COPY ./config/LocalSettings.php /var/www/mediawiki/LocalSettings.php
COPY ./resources /var/www/mediawiki/resources

COPY ./config/php-config.ini /usr/local/etc/php/conf.d/php-config.ini
COPY ./config/robots.txt /var/www/mediawiki/robots.txt
COPY ./resources/assets/favicon.ico /var/www/mediawiki/favicon.ico

RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini; \
    echo 'max_execution_time = 60' >> /usr/local/etc/php/conf.d/docker-php-executiontime.ini; \
	echo 'pm.max_children = 16' >> /usr/local/etc/php-fpm.d/zz-docker.conf; \
    echo 'pm.max_requests = 500' >> /usr/local/etc/php-fpm.d/zz-docker.conf;

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY composer.local.json /var/www/mediawiki

RUN set -eux; \
   chown -R www-data:www-data /var/www; \
   	\
	 mkdir /usr/local/smw; \
	 chown www-data:www-data /usr/local/smw

WORKDIR /var/www/mediawiki

USER www-data

RUN set -eux; \
	# Required till 1.39.4
#    /usr/bin/composer require "guzzlehttp/psr7:2.4.4" --no-install; \ 
#    /usr/bin/composer require "psr/http-message:1.0.1" --no-install; \ 
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
	mv /var/www/mediawiki/extensions/Checkuser /var/www/mediawiki/extensions/CheckUser; \
	mv /var/www/mediawiki/extensions/Dismissablesitenotice /var/www/mediawiki/extensions/DismissableSiteNotice; \
	mv /var/www/mediawiki/extensions/Nativesvghandler /var/www/mediawiki/extensions/NativeSvgHandler; \
	mv /var/www/mediawiki/extensions/Mediasearch /var/www/mediawiki/extensions/MediaSearch; \
	mv /var/www/mediawiki/extensions/Revisionslider /var/www/mediawiki/extensions/RevisionSlider; \
	mv /var/www/mediawiki/extensions/Rss /var/www/mediawiki/extensions/RSS; \
	mv /var/www/mediawiki/extensions/Shortdescription /var/www/mediawiki/extensions/ShortDescription; \
	mv /var/www/mediawiki/extensions/Webauthn /var/www/mediawiki/extensions/WebAuthn; \
	mv /var/www/mediawiki/extensions/WikiSeo /var/www/mediawiki/extensions/WikiSEO; \
	mv /var/www/mediawiki/skins/citizen /var/www/mediawiki/skins/Citizen; \
	mv /var/www/mediawiki/extensions/Twocolconflict /var/www/mediawiki/extensions/TwoColConflict; \
	mv /var/www/mediawiki/extensions/Swiftmailer /var/www/mediawiki/extensions/SwiftMailer; \
	mv /var/www/mediawiki/extensions/mediawiki-extensions-PageViewInfo-REL1_#39 /var/www/mediawiki/extensions/PageViewInfo; \
	chown -R www-data:www-data /var/www

COPY ./config/swiftmailer-extension.json /var/www/mediawiki/extensions/SwiftMailer/extension.json
COPY ./config/smw-extension.json /var/www/mediawiki/extensions/SemanticMediaWiki/extension.json

CMD ["php-fpm"]
