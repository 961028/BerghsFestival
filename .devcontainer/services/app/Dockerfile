FROM php:8.4-apache

ARG PUID
ARG PGID

ENV NODE_MAJOR=25

# Set Timezone

RUN ln -snf /usr/share/zoneinfo/CET /etc/localtime && echo CET > /etc/timezone

# Install Dependencies

RUN apt-get -qy update

RUN apt-get install -qy --no-install-recommends ca-certificates
RUN apt-get install -qy --no-install-recommends curl
RUN apt-get install -qy --no-install-recommends gnupg

RUN mkdir -p /etc/apt/keyrings
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" > /etc/apt/sources.list.d/nodesource.list

RUN apt-get -qy update

RUN apt-get install -qy --no-install-recommends git
RUN apt-get install -qy --no-install-recommends libicu-dev
RUN apt-get install -qy --no-install-recommends libmagickwand-dev
RUN apt-get install -qy --no-install-recommends libzip-dev
RUN apt-get install -qy --no-install-recommends locales-all
RUN apt-get install -qy --no-install-recommends nodejs
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

ARG APP_HOST
ENV APP_HOST ${APP_HOST}

COPY app.conf /etc/apache2/sites-available/
RUN cd /etc/apache2/sites-enabled/ && ln -s ../sites-available/app.conf

RUN a2enmod proxy proxy_http proxy_wstunnel rewrite

# Setup the user

RUN usermod -u ${PUID} www-data
RUN groupmod -g ${PGID} www-data
RUN usermod -s /bin/bash www-data
RUN usermod -d /home/www-data www-data
RUN mkdir /home/www-data && chown -R www-data:www-data /home/www-data

# Done

WORKDIR /srv/app
