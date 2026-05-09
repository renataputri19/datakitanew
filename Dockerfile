# syntax=docker/dockerfile:1.6

# ---------- Stage 1: Build frontend assets with Vite ----------
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json* ./
# Use npm install (not npm ci) because package-lock.json may have been
# generated on a different OS (e.g. Windows) and won't list the Linux
# Rollup native binary that Vite needs. See npm/cli#4828.
RUN npm install --no-audit --no-fund

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY postcss.config.* tailwind.config.* ./

RUN npm run build


# ---------- Stage 2: Install PHP dependencies ----------
FROM composer:2.7 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
COPY database ./database

RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress


# ---------- Stage 3: Production runtime ----------
FROM php:8.2-fpm-alpine AS runtime

# System packages + PHP extensions for Laravel + Maatwebsite Excel + dompdf
RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        curl \
        zip \
        unzip \
        git \
        tini \
        mysql-client \
        icu-dev \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libxml2-dev \
        libzip-dev \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        autoconf \
        g++ \
        make \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        zip \
        gd \
        intl \
        opcache \
        exif \
        pcntl \
        xml \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/*

# Composer binary (for any runtime composer commands)
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application source
COPY . /var/www/html

# Bring in vendor + built frontend assets
COPY --from=vendor   /app/vendor       /var/www/html/vendor
COPY --from=frontend /app/public/build /var/www/html/public/build

# Generate optimized autoloader (composer scripts will run artisan package:discover)
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && php artisan package:discover --ansi || true

# Storage dirs Laravel expects + permissions
RUN mkdir -p storage/framework/{cache,sessions,testing,views} \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Container configs
COPY docker/php.ini             /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php-fpm.pool.conf   /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx.conf          /etc/nginx/nginx.conf
COPY docker/default.conf        /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf    /etc/supervisord.conf
COPY docker/entrypoint.sh       /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && rm -f /etc/nginx/http.d/default.conf.bak

EXPOSE 80

ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
