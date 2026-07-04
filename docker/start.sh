set -e

echo "Aguardando o banco de dados inicializar..."
sleep 15

echo "Executando migrações..."
php /var/www/artisan migrate --force

echo "Executando Seeders..."
php /var/www/artisan db:seed --class=SuperUserSeeder --force

echo "Iniciando o servidor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf