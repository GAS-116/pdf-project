#!/bin/bash

if [ "$ENV" = "local" ]; then
    composer install
fi

php artisan cache:clear --env="$ENV"
php artisan config:clear --env="$ENV"
php artisan optimize:clear --env="$ENV"
php artisan route:clear --env="$ENV"

php artisan migrate --env="$ENV" --force

if [ "$ENV" = "local" ]; then
    if [[ "$PHP_XDEBUG_ENABLE" = 1 ]]; then
        [[ -f /usr/local/etc/php/disabled/php-xdebug.ini ]] && cd /usr/local/etc/php/ && mv disabled/php-xdebug.ini conf.d/
    else
        [[ -f /usr/local/etc/php/conf.d/php-xdebug.ini ]] && cd /usr/local/etc/php/ && mkdir -p disabled/ && mv conf.d/php-xdebug.ini disabled/
    fi
fi

$(php -m | grep -q Xdebug) && echo "Status: Xdebug ENABLED" || echo "Status: Xdebug DISABLED"

/usr/bin/supervisord -c /etc/supervisor/supervisord.conf
