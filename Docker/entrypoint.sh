#!/bin/bash

mysql -hdb -uroot -proot -e "CREATE DATABASE IF NOT EXISTS laravel;"

yes | php artisan migrate --seed

# Start the Laravel development server, accessible from any IP
php artisan serve --host 0.0.0.0