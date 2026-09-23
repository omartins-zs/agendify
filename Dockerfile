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

RUN docker-php-ext-install pdo pdo_pgsql bcmath pcntl opcache

# Instalação do redis via PECL
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS
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

# Copia configurações de performance
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY ./docker/php/fpm-performance.conf /usr/local/etc/php-fpm.d/zz-performance.conf

# Adiciona o script de inicialização inteligente
COPY ./docker/scripts/start-app.sh /usr/local/bin/start-app.sh
RUN chmod +x /usr/local/bin/start-app.sh

CMD ["/usr/local/bin/start-app.sh"]
