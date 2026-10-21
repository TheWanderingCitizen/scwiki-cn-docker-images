#!/bin/sh
set -eu
export COMPOSER_CACHE_DIR=/tmp/scwiki-composer-cache
cd /var/www/mediawiki
composer validate --no-check-publish
composer check-platform-reqs --no-dev
php /workspace/scripts/check-lock.php
git apply --reverse --check /workspace/patches/swiftmailer-6.patch
composer audit --locked --format=json > /tmp/scwiki-audit.json || audit_status=$?
if [ "${audit_status:-0}" -gt 3 ]; then exit "$audit_status"; fi
cat /tmp/scwiki-audit.json
php /workspace/scripts/check-audit.php /tmp/scwiki-audit.json composer.json composer.lock
php /workspace/tests/audit-gate.php
php-fpm -t
pygmentize -V
