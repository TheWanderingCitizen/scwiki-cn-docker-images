FROM php:7.4-fpm

# System dependencies
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN set -eux; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		git \
		librsvg2-bin \
		imagemagick \
		# Required for SyntaxHighlighting
		python3 \
		python3-pygments \
		unzip \
		openssh-client \
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
		libmagickwand-dev \
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
	; \
	docker-php-ext-enable \
		apcu \
		luasandbox \
		imagick  \
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

# SQLite Directory Setup
RUN set -eux; \
	mkdir -p /var/www/data; \
	chown -R www-data:www-data /var/www/data

# Version
ENV MEDIAWIKI_MAJOR_VERSION 1.35
ENV MEDIAWIKI_VERSION 1.35.8
ENV COMPOSER_ALLOW_SUPERUSER 1

# MediaWiki setup
RUN set -eux; \
	\
  	git clone https://github.com/StarCitizenTools/mediawiki.git --single-branch --branch production --recurse-submodules .; \
	chown -R www-data:www-data extensions skins cache images; \
	\
	composer install --no-dev

CMD ["php-fpm"]
