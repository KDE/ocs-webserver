FROM php:5-apache

ENV APPLICATION_ENV development
ENV DEBIAN_FRONTEND noninteractive

ARG BUILD_ENV=production
# Build-Arg BUILD_ENV may be:
# "production" (default)
# "development": installs XDebug

# Exposes XDebug if installed
EXPOSE 9000

# Prepare requisites
RUN apt-get update \
 && apt-get install -y apt-utils \
 && DEBIAN_FRONTEND=noninteractive \
    apt-get install -y build-essential \
    libmemcached-dev libmcrypt-dev zlib1g-dev \
# Memcached and APCU must be installed via pecl
 && yes '' | pecl install memcached-2.2.0 \
 && yes '' | pecl install apcu-4.0.11 \
# MySQL, Mcrypt and Curl via the docker-specific extension-handler
 && docker-php-ext-install mysql mcrypt pdo_mysql \
 && docker-php-ext-enable memcached apcu

# Install Debug-extensions
RUN test "$BUILD_ENV" = "development" \
 && yes '' | pecl install xdebug-2.5.5 \
 || :
# Decouple installation from xdebug-configuration
RUN test "$BUILD_ENV" = "development" \
 && echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so" > $PHP_INI_DIR/conf.d/xdebug.ini \
 && echo "[XDebug]" >> $PHP_INI_DIR/conf.d/xdebug.ini \
 && echo "xdebug.remote_enable = 1" >> $PHP_INI_DIR/conf.d/xdebug.ini \
 && echo "xdebug.remote_autostart = 1" >> $PHP_INI_DIR/conf.d/xdebug.ini \
 && echo "xdebug.remote_connect_back = 0" >> $PHP_INI_DIR/conf.d/xdebug.ini \
 && echo "xdebug.remote_log = /tmp/xdebug.log" >> $PHP_INI_DIR/conf.d/xdebug.ini \
 && echo "xdebug.remote_host = host.docker.internal" >> $PHP_INI_DIR/conf.d/xdebug.ini \
 || :

# Add ocs-webserver sources
COPY --chown=www-data . /usr/src/ocs-webserver
WORKDIR /usr/src/ocs-webserver

# Add the local configuration-template
#COPY ./.docker_init/conf/application.local.ini application/configs/application.local.ini
#COPY ./.docker_init/conf/client_opendesktop.ini.php application/configs/client_opendesktop.ini.php
COPY ./.docker_init/conf/* application/configs/

# Prepare file- & directory-permissions
RUN test "$BUILD_ENV" != "development" \
 && mkdir -vpm 700 cache/data \
 && mkdir -vpm 700 cache/sessions \
 && mkdir -vpm 700 data/cache \
 && mkdir -vpm 700 application/log \
 && mkdir -vpm 700 httpdocs/img/cache/rendered \
 && mkdir -vpm 700 httpdocs/img/cache/request \
 && mkdir -vpm 700 httpdocs/img/data/tmp \
 && chown -vR www-data cache \
 && chown -vR www-data data/cache \
 && chown -vR www-data application/log \
 && chown -vR www-data httpdocs/img/cache \
 && chown -vR www-data httpdocs/img/data \
 || :

# Prepare apache htaccess file and mod_rewrite
RUN a2enmod rewrite
RUN cp httpdocs/_htaccess-default httpdocs/.htaccess

# Replace the default entrypoint of Apache
ENV APACHE_DOCUMENT_ROOT=/usr/src/ocs-webserver/httpdocs
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
