# Star Citizen Tools Docker

MediaWiki 1.39 compatible emergency image for starcitizen.tools / citizenwiki.cn.

The full image pins PHP **8.3.36 FPM Bookworm**, patches PHP2Shell
(CVE-2026-109618 / CVE-2026-109619), disables session upload progress, and
updates MediaWiki to **1.39.17**. The production dependency manifest and lock
are committed in `build/`; normal builds only run `composer install`.

```sh
docker build --pull -t scwiki-mediawiki:php2shell-fixed .
docker run --rm -v "$PWD:/workspace:ro" scwiki-mediawiki:php2shell-fixed sh /workspace/scripts/verify-image.sh
pwsh -NoProfile -File tests/run.ps1
```

The last command creates disposable MariaDB / Redis / Nginx containers and
volumes, installs a test wiki, runs database updates **only in that test wiki**,
and checks semantic queries, Lua, FPM, login and editing. Requires Docker and
PowerShell 7. `-Keep` retains disposable resources for debugging.

See [implementation and deployment plan](docs/upgrade-plan.md),
[complete dependency versions and approved advisory IDs](docs/dependency-versions.md),
[security assessment](docs/dependency-security-review.md), and
[validation status](docs/verification.md).

This is an emergency MW 1.39 build with explicitly approved SMW, EmbedVideo,
DPL3 and WebAuthn exceptions. It does **not** fix those application advisories.
Unknown advisory IDs still block delivery. See `build/audit.json` for the audit
snapshot and `scripts/check-audit.php` for the exact ID/package/version gate.

**Deployment:** the existing `mediawiki-data` volume covers `/var/www` and can
hide the new application code. Recreating containers alone is insufficient.
Back up the database and volumes, follow the release-volume procedure in the
plan, verify the effective code and PHP version, and replace **all PHP workers**.
Never delete the existing volume to make new code appear. No production deploy
or image publication is performed by the local validation commands.

Dependency updates are an explicit maintenance operation:

```sh
python scripts/resolve-dependencies.py
docker build --target source -t scwiki-dependency-source:local .
docker run --rm -v "$PWD:/workspace" scwiki-dependency-source:local sh /workspace/scripts/update-lock.sh
python scripts/report-dependencies.py
```

Review every candidate's real extension requirements and runtime behavior.
The resolver's MW/SMW check is only a prefilter. EOL branches can be absent;
Echo uses a documented historical fallback. Development tools have their own
`tools/composer.json` and lock; they do not affect Parsoid's production graph.

CI validates pull requests and manual runs; the original `smw` release branch
retains Docker Hub `smw-latest` and `smw-YY.MM.DD` publication after validation.
Manual runs on other branches validate without publishing.

References: [PHP security release](https://www.php.net/archive/2026.php#2026-10-21-1),
[OOM corruption advisory](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w),
[stale global state advisory](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp).
