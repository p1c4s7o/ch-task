#!/bin/sh
set -e

APP_DIR="/var/www"
PROJECT_DIR="laravel"
cd $APP_DIR

if [ ! -d "$PROJECT_DIR" ] || [ ! -f "$PROJECT_DIR/artisan" ]; then
  composer create-project laravel/laravel $PROJECT_DIR
  cd $PROJECT_DIR

  composer require symfony/filesystem
  composer require predis/predis
  composer require darkaonline/l5-swagger

  rm .env
  rm .env.example
  cd ../../
  cp -rf ./src/* $APP_DIR/$PROJECT_DIR/
  cp -rf ./src/.env.example $APP_DIR/$PROJECT_DIR/

  cd $APP_DIR/$PROJECT_DIR

  php -r "copy('.env.example', '.env');"
  php artisan key:generate
  php artisan config:clear
#  rm -rf ../../src

  php artisan l5-swagger:generate

fi

cd $APP_DIR/$PROJECT_DIR

exec "$@"