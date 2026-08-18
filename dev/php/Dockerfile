FROM php:8.2-fpm

WORKDIR /var/www/html

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y \
    $PHPIZE_DEPS \
    build-essential \
    locales \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    unzip \
    git \
    curl \
    supervisor \
    libonig-dev \
    zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://github.com/phpredis/phpredis/archive/refs/tags/6.1.0.tar.gz -o /tmp/redis.tar.gz \
    && tar -xzf /tmp/redis.tar.gz -C /tmp \
    && cd /tmp/phpredis-6.1.0 \
    && phpize \
    && ./configure \
    && make -j"$(nproc)" \
    && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/redis.tar.gz /tmp/phpredis-6.1.0

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd intl

RUN echo "upload_max_filesize=20M\npost_max_size=20M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "clear_env = no" >> /usr/local/etc/php-fpm.d/zz-docker.conf

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

COPY . /var/www/html

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi || true

RUN usermod -u 1000 www-data \
    && chown www-data /var/log/supervisor \
    && chown www-data /var/run \
    && mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY dev/php/supervisord.conf /etc/supervisor/conf.d/laravel.conf
COPY dev/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

VOLUME "/var/www/html"

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
