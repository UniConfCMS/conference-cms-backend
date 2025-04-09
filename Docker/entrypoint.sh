#!/bin/bash

composer install

php artisan migrate --seed --force

# Start the Laravel development server, accessible from any IP
php artisan serve --host 0.0.0.0
