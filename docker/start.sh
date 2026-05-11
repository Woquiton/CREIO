#!/bin/bash
set -e

php /var/www/artisan migrate:fresh --force

php /var/www/artisan db:seed --class=SuperUserSeeder --force


exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
