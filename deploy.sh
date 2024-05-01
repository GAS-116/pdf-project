#!/bin/bash
composer install --no-interaction --prefer-dist --optimize-autoloader

if [[ -f artisan ]]
then
    php artisan migrate
fi

npm i
npm run prod

php artisan queue:restart
