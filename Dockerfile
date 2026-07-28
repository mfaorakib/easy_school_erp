# syntax=docker/dockerfile:1
#
# Production image for the easyschool-erp Laravel 12 app, built to run as a
# single Render.com web service: nginx + php-fpm + queue worker + a cron-like
# scheduler loop, all supervised by supervisord inside one container.

############################################################
# Stage: vendor - install PHP (composer) dependencies only
############################################################
FROM composer:2 AS vendor

WORKDIR /app

# Copy only what composer needs so this layer stays cached across app-code
# changes. nwidart/laravel-modules ships wikimedia/composer-merge-plugin,
# configured via composer.json's extra.merge-plugin.include:
# ["Modules/*/composer.json"] - that plugin reads every module's
# composer.json (require/autoload) *during* `composer install`, so the
# Modules/ tree must exist before install runs, even though --no-scripts
# skips the project's own "scripts" hooks (the merge plugin is a real
# Composer plugin, not a scripts hook, so --no-scripts does not disable it).
COPY composer.json composer.lock ./
COPY Modules/ ./Modules/

RUN composer install \
        --no-dev \
        --optimize-autoloader \
        --no-scripts \
        --no-interaction \
        --prefer-dist

############################################################
# Stage: frontend - build Vite/Tailwind assets
############################################################
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

# laravel-vite-plugin needs the public/ directory (it writes
# public/build/manifest.json there) plus the Vite config and the
# module-asset loader referenced by it.
COPY resources/ ./resources/
COPY vite.config.js vite-module-loader.js modules_statuses.json ./
COPY public/ ./public/

RUN npm run build

############################################################
# Stage: app - final production runtime image
############################################################
FROM php:8.2-fpm AS app

# --- System packages + PHP extensions --------------------------------------
# App-required extensions beyond what php:8.2-fpm ships by default:
#   bcmath, exif, gd, intl, mbstring, mysqli, pdo_mysql, zip
# Already built into the base php:8.2-fpm image (no action needed):
#   ctype, curl, dom, fileinfo, json, libxml, openssl, pcre, pdo, tokenizer,
#   xml, xmlwriter
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        gettext-base \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        libonig-dev \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        mysqli \
        pdo_mysql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    # Remove Debian's stock default site: our templated config (rendered at
    # container start into /etc/nginx/conf.d/default.conf) is the only
    # server block that should claim the listening port.
    && rm -f /etc/nginx/sites-enabled/default

WORKDIR /var/www/html

# --- Application source ------------------------------------------------
COPY . .

# PHP dependencies (prod-only, optimized autoloader) from the vendor stage.
COPY --from=vendor /app/vendor ./vendor

# Compiled front-end assets from the frontend stage.
COPY --from=frontend /app/public/build ./public/build

# --- Container runtime scripts/config -----------------------------------
# (Already present from `COPY . .` above; re-declared explicitly so the
# Dockerfile documents exactly which files drive the container's boot.)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN chmod +x /usr/local/bin/entrypoint.sh \
    # Ensure the writable Laravel dirs exist (they ship with only a
    # .gitignore placeholder in each), then hand them to www-data.
    && mkdir -p storage/framework/cache storage/framework/sessions \
        storage/framework/testing storage/framework/views \
        storage/logs storage/app/public storage/app/private \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    # Setgid on every directory so files later created by root (e.g. by
    # entrypoint.sh's `artisan migrate`/`*:cache` calls, which run before
    # supervisord drops the queue-worker/scheduler to www-data) still end
    # up group-owned by www-data instead of root, avoiding permission
    # mismatches between boot-time root writes and runtime www-data writes.
    && find storage bootstrap/cache -type d -exec chmod g+s {} \;

# Render.com supplies $PORT at container runtime, not build time - nginx
# binds to it via docker/entrypoint.sh (envsubst), not a hardcoded value.
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
