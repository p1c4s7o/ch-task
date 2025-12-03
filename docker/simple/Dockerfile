FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    nginx \
    oniguruma-dev \
    curl \
    icu-dev \
    libxml2-dev \
    libzip-dev \
    sqlite-dev \
    $PHPIZE_DEPS

RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring zip

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY docker/simple/scripts/init.sh /init.sh
RUN chmod +x /init.sh

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache supervisor
COPY docker/simple/conf/nginx.conf /etc/nginx/nginx.conf
COPY docker/simple/conf/supervisord.ini /etc/supervisord.conf
COPY src /var/src
COPY docker/simple/patch/. /var/src/

ENTRYPOINT ["/init.sh"]

