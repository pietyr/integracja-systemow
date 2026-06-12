#!/bin/sh
set -e

# Cache z hosta może odwoływać się do pakietów dev niedostępnych w kontenerze.
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php

composer install --ignore-platform-req=ext-sodium --no-interaction

if [ ! -d node_modules ] || [ ! -f public/build/manifest.json ]; then
    npm install
    npm run build
fi

php artisan wayfinder:generate
php artisan migrate --force

php artisan integrations:sync --source=nytimes &

exec php artisan serve --host=0.0.0.0 --port=8000
