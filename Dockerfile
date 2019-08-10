ARG PHP_VERSION=7.3.5
FROM composer:latest as composer
FROM php:${PHP_VERSION}-stretch as php
WORKDIR /app
RUN mkdir -p /usr/src/php/ext/redis \
    && curl -L https://github.com/phpredis/phpredis/archive/5.0.2.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 \
    && echo 'redis' >> /usr/src/php-available-exts \
    && docker-php-ext-install redis
RUN apt-get update && apt-get install -y git zip unzip libpq-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pgsql pdo_pgsql
RUN docker-php-ext-enable redis
COPY ./ /app
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer install
