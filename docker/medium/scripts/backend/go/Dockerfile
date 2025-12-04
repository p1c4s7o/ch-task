FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    gcc \
    g++ \
    musl-dev \
    go

COPY docker/medium/conf/nginx.conf /etc/nginx/nginx.conf
COPY docker/medium/conf/go/supervisord.ini /etc/supervisord.conf

COPY docker/medium/backend-go/. /var/api

WORKDIR /var/api
RUN go mod tidy && \
    CGO_ENABLED=0 go build -o /usr/local/bin/backend main.go

