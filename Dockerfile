FROM starcitizentools/mediawiki:smw-23.10.02.143

USER root

RUN docker-php-ext-configure pcntl --enable-pcntl \
  && docker-php-ext-install pcntl

WORKDIR /var/www/html

USER www-data

RUN git clone https://github.com/miraheze/jobrunner-service.git mediawiki-services-jobrunner 

RUN set -eux; \
   cd mediawiki-services-jobrunner; \
   /usr/bin/composer config --no-plugins allow-plugins.composer/installers true; \
   /usr/bin/composer install --no-dev \
     --ignore-platform-reqs \
     --no-ansi \
     --no-interaction \
     --no-scripts

COPY --chown=www-data:www-data ./jobrunner-conf.json /var/www/html/mediawiki-services-jobrunner
COPY --chown=www-data:www-data --chmod=770 ./entrypoint.sh /var/www/html/mediawiki-services-jobrunner

ENTRYPOINT ["/var/www/html/mediawiki-services-jobrunner/entrypoint.sh"]
