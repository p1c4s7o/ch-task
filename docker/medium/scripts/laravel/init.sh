#!/bin/sh
set -e

APP_DIR="/var/www/laravel"
cd /

if  [ ! -f "$APP_DIR/artisan" ]; then
  cd /
  cp -rf ./src/* $APP_DIR/
  cp -rf ./src/.env.example $APP_DIR/

  cd $APP_DIR
  ls .
  composer install
fi

cd $APP_DIR

if  [ ! -f ".env" ]; then
    php -r "copy('.env.example', '.env');"
    php artisan key:generate
    php artisan cache:clear
    php artisan config:clear
    php artisan config:cache
fi

exec "$@"