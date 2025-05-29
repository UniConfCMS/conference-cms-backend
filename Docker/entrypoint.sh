#!/bin/bash

composer install

php artisan migrate --seed --force


php artisan storage:link

# Встановлюємо дозволи
chmod -R 775 storage
chmod -R 775 public/storage


# Start the Laravel development server, accessible from any IP
php artisan serve --host 0.0.0.0
