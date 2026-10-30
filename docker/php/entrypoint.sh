#!/bin/sh
set -e

cd /var/www/html

[ -f .env ] || cp .env.example .env

[ -d vendor ] || composer install --no-interaction --prefer-dist

grep -q '^APP_KEY=base64' .env || php artisan key:generate --force

php artisan migrate --force

if [ "${SEED_DEMO:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec "$@"