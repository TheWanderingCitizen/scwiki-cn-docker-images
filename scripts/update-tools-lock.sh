#!/bin/sh
# Explicit development-only update; never merge this graph into production.
set -eu
mkdir -p /tmp/scwiki-tools-lock
cd /tmp/scwiki-tools-lock
cp /workspace/tools/composer.json .
composer update --no-install --no-plugins --no-scripts --no-interaction
composer check-platform-reqs --lock
composer audit --locked --format=json > /workspace/tools/audit.json || status=$?
if [ "${status:-0}" -gt 3 ]; then exit "$status"; fi
php /workspace/scripts/check-audit.php /workspace/tools/audit.json
cp composer.lock /workspace/tools/composer.lock
