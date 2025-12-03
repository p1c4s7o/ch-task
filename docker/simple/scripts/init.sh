#!/bin/sh
set -e

APP_DIR="/var/www/html"
cd $APP_DIR

mkdir -p /run/nginx
mkdir -p /var/log/nginx

if [ ! -d "ch" ] || [ ! -f "ch/artisan" ]; then
  composer create-project laravel/laravel ch
  cd ch
  composer require symfony/filesystem
  composer require predis/predis
  composer require darkaonline/l5-swagger

  rm .env
  rm .env.example
  cd ../../
  cp -rf ./src/* html/ch/
  cp -rf ./src/.env.example html/ch/

  cd $APP_DIR/ch

  php -r "copy('.env.example', '.env');"
  php artisan key:generate
  php artisan config:clear
  rm -rf ../../src

  php artisan l5-swagger:generate

fi

exec "$@"