FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    oniguruma-dev \
    curl \
    icu-dev \
    libxml2-dev \
    libzip-dev \
    sqlite-dev \
    $PHPIZE_DEPS

RUN docker-php-ext-install pdo pdo_mysql zip

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY src /src
COPY docker/medium/patch/. /src

RUN chmod +x /src/artisan
WORKDIR /var/www/laravel

COPY docker/medium/scripts/laravel/init.sh /init.sh
RUN chmod +x /init.sh

ENTRYPOINT ["/init.sh"]

#CMD ["php-fpm", "-F"]