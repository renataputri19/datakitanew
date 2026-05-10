# syntax=docker/dockerfile:1.6

# ---------- Stage 1: Build frontend assets with Vite ----------
FROM node:20-alpine AS frontend
WORKDIR /app

# Intentionally NOT copying package-lock.json: when it was generated on
# Windows it carries win32 resolutions for Rollup's optional native deps
# and npm refuses to install the Linux musl binary on Alpine, breaking
# `vite build`. We let npm install resolve fresh against the real platform.
# See npm/cli#4828 and rollup/rollup#5341.
COPY package.json ./
RUN npm install --no-audit --no-fund --include=optional \
    && (npm ls @rollup/rollup-linux-x64-musl >/dev/null 2>&1 \
        || npm install --no-save --no-audit --no-fund @rollup/rollup-linux-x64-musl)

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY postcss.config.* tailwind.config.* ./

# Belt-and-suspenders: drop public/hot if the .dockerignore missed it.
# laravel-vite-plugin treats public/hot as "dev server is running" and
# generates http://[::1]:5173 URLs that 404 in production.
RUN rm -f public/hot \
    && npm run build \
    && echo "=== build output ===" \
    && ls -la public/build/ \
    && ls -la public/build/assets/ \
    && echo "=== manifest.json ===" \
    && cat public/build/manifest.json


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
        --no-progress \
        --ignore-platform-reqs
# --ignore-platform-reqs: the composer:2.7 image lacks ext-gd, ext-intl etc.
# that maatwebsite/excel + phpoffice/phpspreadsheet require. The runtime
# stage (php:8.2-fpm-alpine below) DOES install all required extensions,
# so we only need composer here to resolve and download packages.


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

# Hard-delete public/hot — its presence flips laravel-vite-plugin into
# dev-server mode and serves http://[::1]:5173 URLs that 404 in prod.
RUN rm -f /var/www/html/public/hot

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
