#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

chmod 644 .env || true

echo "Installing PHP dependencies from composer.lock..."
composer install --no-interaction --prefer-dist

echo "Waiting for MySQL..."
until php -r '
try {
    new PDO(
        "mysql:host=" . (getenv("DB_HOST") ?: "mysql") . ";port=" . (getenv("DB_PORT") ?: "3306") . ";dbname=" . (getenv("DB_DATABASE") ?: "shop_api"),
        getenv("DB_USERNAME") ?: "root",
        getenv("DB_PASSWORD") ?: "password"
    );
    exit(0);
} catch (Throwable $e) {
    exit(1);
}
'; do
    sleep 2
done
echo "MySQL is ready."

if ! grep -qE '^APP_KEY=.+' .env 2>/dev/null || grep -qE '^APP_KEY=\s*$' .env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache || true

echo "Creating storage symlink..."
php artisan storage:link || true

echo "Running migrations and seeders..."
php artisan migrate --force --seed

echo "Clearing caches..."
php artisan route:clear
php artisan config:clear

echo "Starting PHP-FPM and queue worker..."
exec supervisord -c /etc/supervisor/conf.d/laravel.conf
