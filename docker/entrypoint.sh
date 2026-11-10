#!/bin/sh
set -e
cd /var/www/html

# Apache's default config also listens on 80; keep only our port.
sed -i 's/^Listen 80$//' /etc/apache2/ports.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force --seed

exec apache2-foreground
