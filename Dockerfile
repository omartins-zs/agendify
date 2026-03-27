FROM php:8.4-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

RUN apk add --no-cache \
    bash \
    git \
    curl \
    nodejs \
    npm \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    libpq-dev \
    linux-headers

RUN docker-php-ext-install pdo pdo_pgsql bcmath pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

COPY . .

RUN mkdir -p storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
