#!/bin/sh
# Run inside the Dockerfile's source target with the repository at /workspace.
set -eu
mkdir -p /tmp/scwiki-lock
cd /tmp/scwiki-lock
cp /workspace/build/composer.json .
composer update --no-dev --prefer-dist --no-interaction --no-scripts
composer check-platform-reqs --no-dev
cp composer.lock /workspace/build/composer.lock
composer audit --locked --format=json > /workspace/build/audit.json || audit_status=$?
if [ "${audit_status:-0}" -gt 3 ]; then exit "$audit_status"; fi
php /workspace/scripts/check-audit.php /workspace/build/audit.json composer.json composer.lock
