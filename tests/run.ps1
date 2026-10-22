param([string]$Image = 'scwiki-mediawiki:main-security-recovery', [string]$DatabaseDump, [switch]$Keep)
$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$prefix = 'scwiki-upgrade-' + [guid]::NewGuid().ToString('N').Substring(0, 8)
$db = "$prefix-db"
$redis = "$prefix-redis"
$wiki = "$prefix-wiki"
$web = "$prefix-web"
$volume = "$prefix-code"
function Invoke-Docker {
    & docker @args
    if ($LASTEXITCODE -ne 0) { throw "Docker command failed: $args" }
}
try {
    Invoke-Docker network create $prefix
    Invoke-Docker run -d --name $db --network $prefix --network-alias db -e MARIADB_ROOT_PASSWORD=local-root-test -e MARIADB_DATABASE=wiki -e MARIADB_USER=wiki -e MARIADB_PASSWORD=local-test-password mariadb@sha256:2439dcd7d14010ecd1ff7a4e1c5abe8e208c34fe35290744deeeaac3569043c3
    Invoke-Docker run -d --name $redis --network $prefix --network-alias redis redis@sha256:becdda6c7f4b3fb42e42fd7f120bbf5c54c4caaaf16f26da24e4563d2c1f0576
    $ready = $false
    for ($i = 0; $i -lt 60; $i++) {
        & docker exec $db healthcheck.sh --connect --innodb_initialized 2>$null | Out-Null
        if ($LASTEXITCODE -eq 0) { $ready = $true; break }
        Start-Sleep -Seconds 1
    }
    if (!$ready) { throw 'Disposable database did not become ready' }
    Invoke-Docker volume create $volume
    Invoke-Docker run -d --name $wiki --network $prefix --network-alias mediawiki --mount "type=volume,source=$volume,target=/var/www/mediawiki" --mount "type=bind,source=$root/tests,target=/test,readonly" --mount "type=bind,source=$root/config/LocalSettings.php,target=/production-settings.php,readonly" $Image sleep infinity
    Invoke-Docker exec $wiki mkdir -p /tmp/bootstrap
    Invoke-Docker exec $wiki mv /var/www/mediawiki/LocalSettings.php /tmp/original-settings.php
    if ($DatabaseDump) {
        $scoped = Join-Path $root 'production-rehearsal/citizenwiki.sql'
        & python (Join-Path $root 'scripts/extract-wiki-dump.py') $DatabaseDump --output $scoped
        if ($LASTEXITCODE -ne 0) { throw 'Database extraction failed' }
        Invoke-Docker cp $scoped "${db}:/tmp/rehearsal.sql"
        # This user can access only the disposable wiki database, not mysql or other databases.
        Invoke-Docker exec $db sh -c 'mariadb --binary-mode=1 -uwiki -plocal-test-password wiki < /tmp/rehearsal.sql'
        Invoke-Docker exec $db rm /tmp/rehearsal.sql
    } else {
        Invoke-Docker exec $wiki php maintenance/install.php --confpath /tmp/bootstrap --dbtype mysql --dbserver db --dbname wiki --dbuser wiki --dbpass local-test-password --dbprefix cnwiki_ --server http://127.0.0.1:8080 --scriptpath= --pass 'Local-test-password-2026!' 'Upgrade smoke' UpgradeAdmin
    }
    Invoke-Docker exec $wiki cp /test/LocalSettings.php /var/www/mediawiki/LocalSettings.php
    Invoke-Docker exec $wiki php maintenance/update.php --quick
    if ($DatabaseDump) {
        Invoke-Docker exec $wiki php maintenance/createAndPromote.php --sysop UpgradeAdmin 'Local-test-password-2026!'
    }
    Invoke-Docker exec -e TEST_FULL_EXTENSION_BOOT=1 $wiki php /test/full-boot.php
    Invoke-Docker exec $wiki php /test/smoke.php
    if ($DatabaseDump) { Invoke-Docker exec $wiki php /test/production-smoke.php }
    Invoke-Docker exec $wiki php maintenance/runJobs.php --maxjobs 30 --memory-limit 512M
    Invoke-Docker exec $wiki php-fpm -t
    Invoke-Docker exec $wiki cp /test/fpm.php /var/www/mediawiki/upgrade-fpm-probe.php
    Invoke-Docker exec -d $wiki php-fpm -F
    Invoke-Docker run -d --name $web --network $prefix --network-alias web --mount "type=volume,source=$volume,target=/var/www/mediawiki,readonly" --mount "type=bind,source=$root/config/nginx/default.conf,target=/etc/nginx/conf.d/default.conf,readonly" nginx@sha256:552e7481ca93ffccd046aa658dbbed22caefbc09c66fa7cd247cbb90b8a5c609
    Invoke-Docker exec $web nginx -t
    Start-Sleep -Seconds 1
    Invoke-Docker exec -e TEST_BASE_URL=http://web $wiki php /test/http.php
    Invoke-Docker exec $wiki rm /var/www/mediawiki/upgrade-fpm-probe.php
    Write-Output 'ALL ISOLATED INTEGRATION CHECKS PASSED'
} catch {
    & docker logs --tail 35 $wiki 2>$null
    throw
} finally {
    if (!$Keep) {
        & docker rm -fv $web $wiki $redis $db 2>$null | Out-Null
        & docker volume rm $volume 2>$null | Out-Null
        & docker network rm $prefix 2>$null | Out-Null
    } else { Write-Output "Kept disposable containers: $wiki, $redis, $db" }
}
