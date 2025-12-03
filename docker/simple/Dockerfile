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

RUN mkdir -p /run/nginx \
    && mkdir -p /var/www/html

COPY docker/simple/scripts/init.sh /init.sh
RUN chmod +x /init.sh

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache supervisor
COPY docker/simple/conf/nginx.conf /etc/nginx/nginx.conf
COPY docker/simple/conf/supervisord.ini /etc/supervisord.conf
COPY src /var/www/src
COPY docker/simple/patch/nginx.php /var/www/src/config/nginx.php
COPY docker/sites/default.conf /etc/nginx/conf.d/
COPY docker/www/default /var/www/html

ENTRYPOINT ["/init.sh"]

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
