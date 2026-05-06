#!/bin/bash
set -e

php /var/www/artisan migrate --force

php /var/www/artisan db:seed --class=SuperUserSeeder --force

if [ "${SEED_DEMO}" = "true" ]; then
    php /var/www/artisan db:seed --class=DemoSeeder --force
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
