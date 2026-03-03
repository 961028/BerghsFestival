FROM php:8.4-apache

ARG PUID
ARG PGID

# Set Timezone

RUN ln -snf /usr/share/zoneinfo/CET /etc/localtime && echo CET > /etc/timezone

# Install Dependencies

RUN apt-get -qy update
RUN apt-get install -qy --no-install-recommends git
RUN apt-get install -qy --no-install-recommends libicu-dev
RUN apt-get install -qy --no-install-recommends libmagickwand-dev
RUN apt-get install -qy --no-install-recommends libzip-dev
RUN apt-get install -qy --no-install-recommends locales-all
RUN apt-get install -qy --no-install-recommends unzip
RUN docker-php-ext-install exif
RUN docker-php-ext-install intl
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install opcache
RUN docker-php-ext-install zip
RUN pecl install imagick
RUN docker-php-ext-enable imagick
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# Install Composer

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy files

COPY xdebug.ini /usr/local/etc/php/conf.d/app-xdebug.ini

# Configure Apache

ENV APACHE_DOCUMENT_ROOT /srv/app/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN a2enmod rewrite

# Setup the user

RUN usermod -u ${PUID} www-data
RUN groupmod -g ${PGID} www-data
RUN usermod -s /bin/bash www-data
RUN usermod -d /home/www-data www-data
RUN mkdir /home/www-data && chown -R www-data:www-data /home/www-data

# Done

WORKDIR /srv/app
