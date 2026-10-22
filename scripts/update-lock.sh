#!/bin/sh
# Explicit maintenance only. Normal image builds never update dependencies.
set -eu
mkdir -p /tmp/scwiki-lock
cd /tmp/scwiki-lock
cp /workspace/build/composer.json .
composer update --no-dev --no-install --no-plugins --no-scripts --no-interaction
composer check-platform-reqs --lock --no-dev
composer audit --locked --format=json > /workspace/build/audit.json || status=$?
if [ "${status:-0}" -gt 3 ]; then exit "$status"; fi
php /workspace/scripts/check-audit.php /workspace/build/audit.json
cp composer.lock /workspace/build/composer.lock
