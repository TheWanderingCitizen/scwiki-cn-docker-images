# Builder stage
FROM mediawiki:1.43-fpm AS builder

# Version
ARG MEDIAWIKI_MAJOR_VERSION='1.43'
ARG MEDIAWIKI_VERSION='1.43.3'

# Build arguments
ARG UPDATE_SYSTEM_DEPENDENCIES=false
ARG UPDATE_PHP_EXTENSIONS=false
ARG UPDATE_COMPOSER_DEPENDENCIES=false

# System dependencies
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
	--mount=type=cache,target=/var/lib/apt,sharing=locked \
	set -eux; \
	echo "Updating system dependencies: ${UPDATE_SYSTEM_DEPENDENCIES}"; \
	apt-get update; \
	apt-get install -y --no-install-recommends \
		git \
		unzip \
		openssh-client \
		# Required to build Pygments
		python3 \
		python3-pip \
	;

# Pygments
# Required for Extension:SyntaxHighlight
# Try apt package first, fallback to pip if needed
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends python3-pygments || \
	(pip3 install --trusted-host pypi.org --trusted-host pypi.python.org --trusted-host files.pythonhosted.org Pygments --break-system-packages) \
	;

# Create a tarball of the Python packages so that we can copy them to the final image
RUN export PY_PACKAGES_PATH=$(python3 -c 'import sysconfig; print(sysconfig.get_path("platlib"))') && \
	tar -czf /python-packages.tar.gz -C ${PY_PACKAGES_PATH} .

# PHP extensions
# install-php-extensions is used for simplicity since it also supports pecl and it can install wikidiff2 correctly
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN --mount=type=cache,target=/tmp/phpexts-cache \
	set -eux; \
	echo "Updating PHP extensions: ${UPDATE_PHP_EXTENSIONS}"; \
	install-php-extensions \
		exif \
		imagick \
		zip \
		redis \
		wikidiff2 \
	;

# MediaWiki is already installed in the base image at /var/www/html
# Copy composer from the official composer image
COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Skins and extensions
# Defined in composer.local.json
COPY ./composer.local.json /var/www/html/composer.local.json

RUN set -eux; \
	mkdir /usr/local/smw; \
	mkdir -p /var/www/.composer; \
	chown -R www-data:www-data /var/www/html /usr/local/smw /var/www/.composer

USER www-data

RUN --mount=type=cache,target=/var/www/.composer/cache,uid=33,gid=33 \
	set -eux; \
	echo "Forcing composer update: ${UPDATE_COMPOSER_DEPENDENCIES}"; \
	/usr/bin/composer config --no-plugins allow-plugins.composer/installers true; \
	\
	# Install the skins and extensions first
	/usr/bin/composer install --no-dev \
		--prefer-source \
		--ignore-platform-reqs \
		--no-ansi \
		--no-interaction \
		--no-scripts; \
	\
	# Remove composer.lock so the next command won't use it
	rm -f composer.lock; \
	\
	# Needed so that composer would install the depedencies of the skins and extensions that we just installed
	/usr/bin/composer update --no-dev \
		--prefer-source \
		--no-ansi \
		--no-interaction \
		--no-scripts;

# Final image
FROM mediawiki:1.43-fpm

ARG UPDATE_SYSTEM_DEPENDENCIES=false
ARG UPDATE_PHP_EXTENSIONS=false

# Runtime dependencies
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
	--mount=type=cache,target=/var/lib/apt,sharing=locked \
	set -eux; \
	echo "Updating system dependencies: ${UPDATE_SYSTEM_DEPENDENCIES}"; \
	\
	apt-get update; \
	apt-get install -y --no-install-recommends \
		# Sysops tools
		openssh-client \
		nano \
		rsync \
		s3cmd \
		unzip \
		# MediaWiki requirements
		imagemagick \
		# Required to show commit info in Special:Version
		git \
		# Extension:EmbedVideo
		ffmpeg \
		# Extension:SyntaxHighlight
		python3 \
		# Extension:Thumbro
		libvips-tools \
	;

# PHP extensions
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN --mount=type=cache,target=/tmp/phpexts-cache \
	set -eux; \
	echo "Updating PHP extensions: ${UPDATE_PHP_EXTENSIONS}"; \
	install-php-extensions \
		exif \
		imagick \
		zip \
		redis \
		wikidiff2 \
	;

# Copy PHP configs
COPY ./config/php-config.ini /usr/local/etc/php/conf.d/php-config.ini
COPY ./config/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
RUN echo 'memory_limit = 256M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini; \
    echo 'max_execution_time = 120' >> /usr/local/etc/php/conf.d/docker-php-executiontime.ini; \
    echo 'max_input_vars = 3000' >> /usr/local/etc/php/conf.d/docker-php-maxinputvars.ini; \
    echo 'post_max_size = 64M' >> /usr/local/etc/php/conf.d/docker-php-uploads.ini; \
    echo 'upload_max_filesize = 64M' >> /usr/local/etc/php/conf.d/docker-php-uploads.ini; \
    echo 'pm = dynamic' >> /usr/local/etc/php-fpm.d/zz-docker.conf; \
    echo 'pm.max_children = 32' >> /usr/local/etc/php-fpm.d/zz-docker.conf; \
    echo 'pm.start_servers = 8' >> /usr/local/etc/php-fpm.d/zz-docker.conf; \
    echo 'pm.min_spare_servers = 4' >> /usr/local/etc/php-fpm.d/zz-docker.conf; \
    echo 'pm.max_spare_servers = 16' >> /usr/local/etc/php-fpm.d/zz-docker.conf; \
    echo 'pm.max_requests = 1000' >> /usr/local/etc/php-fpm.d/zz-docker.conf; \
    echo 'pm.process_idle_timeout = 60s' >> /usr/local/etc/php-fpm.d/zz-docker.conf; \
    echo 'request_terminate_timeout = 120s' >> /usr/local/etc/php-fpm.d/zz-docker.conf;

# Create required directories
RUN mkdir -p /var/www/html /usr/local/smw; \
	chown www-data:www-data /usr/local/smw

WORKDIR /var/www/html

# Copy built application files and python packages from the builder stage
COPY --from=builder /var/www/html /var/www/html
COPY --from=builder /python-packages.tar.gz /python-packages.tar.gz
RUN export PY_PACKAGES_PATH=$(python3 -c 'import sysconfig; print(sysconfig.get_path("platlib"))') && \
	mkdir -p ${PY_PACKAGES_PATH} && \
	tar -xzf /python-packages.tar.gz -C ${PY_PACKAGES_PATH} && \
	rm /python-packages.tar.gz
COPY --from=builder /usr/local/bin/pygmentize /usr/local/bin/pygmentize

# Copy final configs
COPY ./config/LocalSettings.php /var/www/html/LocalSettings.php
COPY ./resources /var/www/html/resources
COPY ./config/robots.txt /var/www/html/robots.txt

# Copy extentions
RUN set -eux; \
	# Rename extensions if they exist (only the ones installed via composer)
	if [ -d "/var/www/html/extensions/Checkuser" ]; then mv /var/www/html/extensions/Checkuser /var/www/html/extensions/CheckUser; fi; \
	if [ -d "/var/www/html/extensions/Dismissablesitenotice" ]; then mv /var/www/html/extensions/Dismissablesitenotice /var/www/html/extensions/DismissableSiteNotice; fi; \
	if [ -d "/var/www/html/extensions/Nativesvghandler" ]; then mv /var/www/html/extensions/Nativesvghandler /var/www/html/extensions/NativeSvgHandler; fi; \
	if [ -d "/var/www/html/extensions/Mediasearch" ]; then mv /var/www/html/extensions/Mediasearch /var/www/html/extensions/MediaSearch; fi; \
	if [ -d "/var/www/html/extensions/Revisionslider" ]; then mv /var/www/html/extensions/Revisionslider /var/www/html/extensions/RevisionSlider; fi; \
	if [ -d "/var/www/html/extensions/Rss" ]; then mv /var/www/html/extensions/Rss /var/www/html/extensions/RSS; fi; \
	if [ -d "/var/www/html/extensions/Shortdescription" ]; then mv /var/www/html/extensions/Shortdescription /var/www/html/extensions/ShortDescription; fi; \
	if [ -d "/var/www/html/extensions/Webauthn" ]; then mv /var/www/html/extensions/Webauthn /var/www/html/extensions/WebAuthn; fi; \
	if [ -d "/var/www/html/skins/citizen" ]; then mv /var/www/html/skins/citizen /var/www/html/skins/Citizen; fi; \
	if [ -d "/var/www/html/extensions/Twocolconflict" ]; then mv /var/www/html/extensions/Twocolconflict /var/www/html/extensions/TwoColConflict; fi; \
	if [ -d "/var/www/html/extensions/Swiftmailer" ]; then mv /var/www/html/extensions/Swiftmailer /var/www/html/extensions/SwiftMailer; fi; \
	if [ -d "/var/www/html/extensions/Templatesandbox" ]; then mv /var/www/html/extensions/Templatesandbox /var/www/html/extensions/TemplateSandbox; fi; \
	if [ -d "/var/www/html/extensions/Usergroups" ]; then mv /var/www/html/extensions/Usergroups /var/www/html/extensions/UserGroups; fi; \
	\
	# Copy custom file if the extension exists
	if [ -f "/var/www/html/extensions/PictureHtmlSupport/includes/ThumbnailImage.php" ]; then \
		cp /var/www/html/extensions/PictureHtmlSupport/includes/ThumbnailImage.php /var/www/html/includes/media/ThumbnailImage.php; \
	fi;

# Set final ownership
RUN chown -R www-data:www-data /var/www/html

USER www-data

CMD ["php-fpm"]