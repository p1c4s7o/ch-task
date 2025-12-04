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


RUN apk add --no-cache supervisor
COPY docker/medium/conf/nginx.conf /etc/nginx/nginx.conf
COPY docker/medium/conf/php/supervisord.ini /etc/supervisord.conf
COPY docker/medium/backend/. /var/api

EXPOSE 9000