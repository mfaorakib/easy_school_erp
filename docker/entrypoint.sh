#!/usr/bin/env bash
#
# Container entrypoint for the easyschool-erp production image. Runs once
# per container start, before supervisord takes over as PID 1's successor
# process. Anything that depends on real runtime env vars (DB_*, APP_KEY,
# Render's $PORT, ...) has to happen here rather than at `docker build`
# time, since those values only exist once the container is actually
# running.
set -e

cd /var/www/html

# New files created below (log files, cache files, the storage:link
# symlink, ...) are written while this script runs as root; setgid bits
# baked into storage/ and bootstrap/cache/ at image build time make those
# files inherit the www-data group, and this umask keeps them
# group-writable, so the www-data-run nginx/php-fpm/queue-worker/scheduler
# processes can still read and write them without a permission mismatch.
umask 002

# --- Render injects $PORT at runtime; template nginx's server block ----
# Only ${PORT} is substituted - passing an explicit variable list to
# envsubst leaves every other "$..." token in the template (nginx's own
# variables) untouched.
: "${PORT:=10000}"
export PORT
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# --- One-time Laravel boot tasks -----------------------------------------
# Defensive: never trust a config cache that got baked into the image:
php artisan config:clear

# Defensive: (re)build the package-discovery manifest (bootstrap/cache/
# packages.php + services.php) from what's actually in vendor/ right now.
# .dockerignore keeps any stale, locally-generated copy of these files out
# of the image in the first place, but Laravel only auto-regenerates them
# when the file is missing, not when it's present-but-stale - so this call
# is the thing that actually guarantees correctness, not just a nice-to-have.
php artisan package:discover --ansi

# Laravel migrations are tracked/idempotent - safe to run on every boot
# against the external MySQL database Render provides via DB_* env vars.
php artisan migrate --force

# Render's free tier disk is ephemeral, so this symlink is gone on every
# fresh container start/restart; guard against it already existing so a
# restart within the same container's lifetime doesn't error out.
[ -L public/storage ] || php artisan storage:link

# Real env vars ARE available now (unlike at image build time), so it's
# safe to bake production performance caches at this point.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- Hand off to supervisord (nginx, php-fpm, queue worker, scheduler) --
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
