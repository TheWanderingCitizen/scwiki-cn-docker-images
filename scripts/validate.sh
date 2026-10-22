#!/bin/sh
set -eu
cd /var/www/mediawiki
composer validate --no-check-publish
composer check-platform-reqs --no-dev
composer audit --locked --format=json > /tmp/production-audit.json || status=$?
if [ "${status:-0}" -gt 3 ]; then exit "$status"; fi
php /checks/scripts/check-audit.php /tmp/production-audit.json
php /checks/scripts/verify-installed.php
php /checks/tests/audit-gate.php
php-fpm -t
php -r 'if (PHP_VERSION !== "8.3.36" || posix_geteuid() !== 33 || ini_get("session.upload_progress.enabled")) { exit(1); }'
cd /checks/tools
composer validate --no-check-publish
composer check-platform-reqs --lock
composer audit --locked --format=json > /tmp/tools-audit.json || tool_status=$?
if [ "${tool_status:-0}" -gt 3 ]; then exit "$tool_status"; fi
php /checks/scripts/check-audit.php /tmp/tools-audit.json
