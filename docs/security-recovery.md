# MW 1.43 security recovery

## Scope and release status

Started from freshly fetched `main` commit `7cb68f167425b0cbe0aa17c1a7937e314c3f559c`.
PR #5 remains closed. This replaces the cancelled MW 1.39 work. No production
advisory exceptions from that work are retained. Do not restore its application
artifacts or SwiftMailer patch.

The complete candidate builds and passes fresh-install and restored-database
checks described below. **It is not yet cleared for publication or production
deployment.** `build/release-gates.json` records the outstanding gates; CI refuses
publication while any gate or its evidence is missing. A clean audit alone is
not release authorization. No merge or production deployment is automatic.

## Baseline evidence

The user identified production as `main-25.09.12.40`. Registry inspection found:

| Evidence | Value |
|---|---|
| OCI index digest | `sha256:43aed5b54cb1729e3d1d4e38fc6105241c96c1f33ee42de9eb887872fc29f1f6` |
| Linux amd64 manifest | `sha256:176c7690b1dc6f04a457430bdc149dc3c5eb982e5923ed98383ef53038bcbbfe` |
| Image PHP / MW / SMW | 8.3.25 / 1.43.3 / 6.0.1 |
| Image production lock | 166 packages; several synthetic versions/floating refs |

The inspected image lock reports **37 advisories across 11 packages**;
`build/deployed-image-audit.json` retains that report. Inherited aliases and
synthetic versions limit advisory matching in this old lock, so this count is
not a claim that every extension issue was enumerated.

These are **image filesystem** findings. The running application volume can hide
that filesystem; its actual inventory must still be collected before release.
`build/deployed-image-inventory.json` stores only package/version/reference
metadata. No production keys, credentials, rows or module contents are committed.

The user confirmed the workspace dump is the latest production backup. Despite
its `.gz` suffix it is plain SQL with binary column bytes. It contains other
databases, including `mysql` and `flarum`. Rehearsal extracts only `citizenwiki`
(136 tables) and imports under a disposable account limited to one database.
View definers are changed to `CURRENT_USER` in the private extracted copy.
The original dump is untouched. Backup and exports are ignored by Git and Docker.

The supplied database contains four TOTP enrollments, one belonging to an
administrator. This is an inventory check, not proof of a successful login.
Legacy `/usr/local/smw/.smw.json` was not supplied; the inspected old image's
state directory was empty. Survivor flags and upgrade state therefore remain
unverified even though the copied database upgrades successfully.

## Changes and compatibility

| Component | Baseline | Candidate |
|---|---|---|
| PHP | 8.3.25 in deployed image; floating Docker input | 8.3.36 FPM Bookworm, fixed digest |
| MediaWiki | 1.43.3 | 1.43.11, signed release verified |
| Parsoid | core bundle | 0.20.10 |
| Guzzle / PSR7 / promises | 7.9.2 / 2.8.0 / 2.3.0 in image | 7.16.0 / 2.13.2 / 2.6.0 |
| PSR HTTP message / YAML | baseline image lock | 1.1 / 5.4.54 |
| AWS SDK / S3 extension | baseline image lock | 3.401.5 / 0.14.0 |
| SMW | 6.0.1, Composer called it dev-master | 7.3.1 |
| SESP / SRF / SemanticScribunto | floating development refs | 5.0.2 / 6.0.0 / 3.0.0 |
| EmbedVideo / DPL | dev-master / 4.0.0-alpha.5 | 4.2.0 / stable DPL4 4.0.0 |
| Apiunto | unavailable alistair3149 fork, manifest 3.0.0 | StarCitizenTools 3.0.1 plus site adapter |
| Developer tools | core/dependency constraints mixed | isolated codesniffer 48.0.1, PHPCS 3.13.6, PHPUnit 9.6.34 |

Every package, including unchanged, added and removed dependencies, is listed in
`dependencies/inventory.json`. `build/extension-inventory.json` records genuine
manifest versions, MW/PHP/extension constraints and source SHAs for all 59 managed
extension/skin inputs. Extensions without upstream numeric versions use their
real development branch identifier and an immutable commit, not `1.43.0` aliases.
Canonical Composer names replace six inherited aliases so advisories match the
actual upstream package. The Apiunto package identity changes with its source.

Official packages use compatible REL1_43 commits where available. Independent
packages use verified stable targets. The source and archive references agree.
Purge's URL is corrected and its release commit pinned. Disabled extensions stay
disabled; Graph, WebP and PictureHtmlSupport are removed from the dependency
graph. The core ThumbnailImage.php replacement is removed. Core Imagick WebP
conversion remains available and is tested.

The shared PHP base enforces version 8.3.36. Session upload progress is disabled
before request parsing; FPM runs as www-data. Existing FPM worker/capacity settings
are retained. Composer 2.10.3, the extension installer, all PECL versions and
Pygments 2.21.0 are pinned; SyntaxHighlight names that executable explicitly.
Bookworm packages are updated inside Bookworm, without a Debian release change.
Production exception details, SQL dumps and debug comments are disabled; PHP's
server-side error logging remains enabled.

### Site compatibility code

`compat/` is a small site extension copied to `recovery/` in the image. It does
not rewrite upstream PHP or stored wiki pages.

* `_LINKSTO` retains its historical ID, label, aliases, subject namespaces
  (Template/Module), target namespaces, same-DB-key exclusion and existing-target
  requirement. Its callback uses SMW 7 classes and joins `pagelinks.pl_target_id`
  to `linktarget`. The SESP definitions generator asserts the pinned native ID
  `___LINKSTO`, then removes only that definition before adding the site callback.
  Tests cover self links, same-name links across namespaces, wrong namespaces,
  missing targets and the absence of the conflicting native property.
* SMW uses extension registration; the former `enableSemantics()` namespace value
  `.citizenwiki.cn` is preserved exactly. Namespace IDs are unchanged. Migration
  of the legacy installation-state file still requires a restored copy.
* The Apiunto adapter was derived from the inspected deployed implementation and
  current production modules. It keeps `get_raw`, the old helper paths (including
  the historical armor-path quirk), the four forwarded arguments, default locale,
  include/locale array encoding and raw response strings. The native `fetch`
  interface is also available. Network/cache handling uses the maintained source.
  Redirects are disabled for the production source, as in the candidate config.
* VehicleHardpoint requests `limit=1000`. The SemanticScribunto adapter handles
  that exact array argument with two ordinary 500-row inline queries, preserving
  offsets and row shape. All other calls use upstream behavior. A 501-result,
  uniquely sorted fixture verifies that neither truncation nor duplicates occur.
  The global inline query limit is unchanged. Large production output and timing
  still need sign-off against representative page results.
* DPL4 is a rewrite and a rename. Its load name changes to DynamicPageList4;
  existing DPL configuration is retained. Actual production DependencyList/DPL
  execution passes. Review other production DPL reports before reopening writes.
* SwiftMailer's current REL1_43 code uses Symfony Mailer. Its manifest registers
  `SymfonyMailer`, but its transport requests `SwiftMailer`; a configuration alias
  fixes that upstream mismatch. Local SMTP delivery passes. No SwiftMailer 6
  patch is carried forward. Its inverted `SMTPTlsPeerVerification` flag must never
  be true in production; deployment must verify TLS and credentials explicitly.

WebAuthn is removed from dependencies/loading. OATHAuth/TOTP and local/OIDC login
remain. No enrolled-key tables are dropped. Do not remove credential data or
disable second-factor requirements as a deployment workaround.

## Security policy and reproducibility

There are no production advisory exceptions. The old `audit.block-insecure=false`
override is removed. Build-time dependency updates, lock deletion and platform
ignores are removed. The production manifest contains explicit, reviewable
overrides for Guzzle, PSR7, promises, HTTP message, YAML and AWS; the rest follows
the chosen core release. Composer installers 1.12.0 satisfies the bundled
ImageMap/Vector constraint intersection.

`scripts/prepare-dependencies.py` is an explicit maintenance command that refreshes
source metadata; it is never run during image builds. Review its generated diff.
`scripts/update-lock.sh` resolves the complete production lock in the pinned source
environment. Final builds use `composer install` only. The installed verifier
checks every version/source/archive reference and requirements from all 88
upstream runtime manifests, including nested manifests, excluding test fixtures.
RecoveryCompat is registered separately by the site configuration.

Current production graph: 168 packages, zero reported advisories, no abandoned
packages. Development graph: 82 packages, zero reported advisories;
`doctrine/cache` remains abandoned through development tools. It is isolated from
production. Keep the abandoned inventory even when Composer audit returns a
nonzero status solely for abandonment. The audit checker examines JSON structure,
advisory entries and filtering, rather than treating an exit code as sufficient.
Network/audit failures and unknown advisories fail delivery.

The published 1.43.3 core bundle had 17 reported advisories; its audit is retained
as baseline evidence, not represented as the complete deployed audit. The
candidate audit reports and installed-image inventory supersede that baseline.

## Reproduce local validation

```powershell
docker build --target source -t scwiki-main-source:local .
# Explicit updater only; review generated changes before building a release.
python scripts/prepare-dependencies.py
docker run --rm -v "${PWD}:/workspace" --entrypoint sh scwiki-main-source:local /workspace/scripts/update-lock.sh
docker build -t scwiki-mediawiki:main-security-recovery .
docker run --rm -v "${PWD}:/checks:ro" --entrypoint sh scwiki-mediawiki:main-security-recovery /checks/scripts/validate.sh
./tests/run.ps1
./tests/run.ps1 -DatabaseDump 'ABSOLUTE_LOCAL_BACKUP_PATH'
docker build --no-cache-filter builder -t scwiki-mediawiki:locked-repeat .
docker run --rm -v "${PWD}/scripts:/checks:ro" --entrypoint php scwiki-mediawiki:locked-repeat /checks/verify-installed.php
```

Tests create unique isolated MariaDB/Redis/Nginx services and app volumes. Cleanup
removes only those resources, including anonymous test DB volumes. `-Keep` retains
them for diagnosis. Never point the test settings or dump scripts at production.
External AWS/OIDC/PluggableAuth/Discord/MultiPurge loading is omitted from these
offline tests, while offline AWS signing and all dependencies are still checked.

Passed checks include complete image build, lock consistency, full platform
requirements, all installed versions/references, live audit and its negative
cases, PHP/FPM UID/SAPI/upload-progress settings, Imagick WebP conversion,
fresh installation, copied database update and repeated update, SMW/SESP/Lua/SRF,
DPL/EmbedVideo, SyntaxHighlight, JWT and CheckUser tokens, native and legacy Apiunto
against a local fixture, synchronous/asynchronous/redirect HTTP, PSR streams,
YAML, SMTP delivery, local login/edit/render/upload and background jobs.

The copied production workload exhausted MW's default 150 MiB maintenance memory
on one refresh job. Job tests now explicitly use `--memory-limit 512M`; FPM
capacity is unchanged. The affected page's refresh was rerun successfully at
512 MiB. This is a bounded CLI setting, not an unlimited memory workaround.

Still required: legacy SMW state migration, existing administrator TOTP/recovery
login, complete representative production report/output comparisons, deployed
OIDC, S3 and SMTP/TLS, actual mounted-code inventory, uploads/volume restoration
and a matching rollback rehearsal. Do not mark these passed merely because the
database schema or generated-key TOTP tests passed.

## Deployment runbook (operator action, not performed here)

1. Keep service in maintenance mode. Record actual PHP and job image digests,
   `docker inspect` mounts, PHP version, MW/extension versions and installed
   Composer references from the running application filesystem. Avoid printing
   environment credentials. Save inventory privately. Confirm no stale workers.
2. Stop writes and job consumers. Back up the database, the complete current app
   volume, uploads, configuration/secrets and `/usr/local/smw`. Test restoration
   into an isolated environment. Obtain `.smw.json` from production; preserve its
   incomplete-task flags and install state. The backup supplied here does not
   contain this file. Current Compose does not persist `/usr/local/smw`; copy
   state out of the existing production PHP container's writable layer before
   replacing that container. Review production Lua/pages/SRF statistical and renamed
   chart parameters against expected outputs, without editing live content.
3. Demonstrate an existing administrator's TOTP or recovery-code login, using
   the same authentication configuration intended for deployment. Verify OIDC,
   S3 read/upload/delete and SMTP certificate verification/delivery. Do not use
   test accounts or a mere enrollment count as evidence of existing access.
4. Prepare both the validated application and a rollback application using
   **patched PHP**. An unmodified `main-25.09.12.40` image is not a safe rollback.
   An older application that retains known vulnerabilities stays in maintenance
   mode until repaired. Match rollback code with its pre-upgrade database backup.
5. Populate a **new**, versioned application volume from the validated image.
   The existing `mediawiki-app:/var/www/mediawiki` volume hides the image's code;
   pulling a new image does not update that nonempty volume. Do not delete it.
   Keep uploads separate and use a fresh disposable cache volume. Restore a copy
   of SMW state into a separate volume. Both PHP and Nginx must mount the same
   new app volume. Verify that the mounted LocalSettings matches this release.
6. `compose.recovery.yml` provides required, explicit image/app/cache/SMW volume
   names. Set `RECOVERY_IMAGE` to an immutable validated digest, and the three
   `RECOVERY_*_VOLUME` variables. First seed the new app volume using a temporary
   stopped container created from that exact image with the empty app volume
   mounted at `/var/www/mediawiki`, then remove only that temporary container.
   Check the new volume's manifest before running maintenance. Never reuse an
   old app volume name or copy its old vendor/core over the candidate.
7. During the write outage, run the rehearsed `maintenance/update.php --quick`
   against the backed-up database with restored SMW state mounted. Inspect
   `smw_meta` and migration of `.smw.json` to `.smw.json.migrated`. If SMW fulltext
   search is enabled, run its required fulltext rebuild. Retain migration logs.
   Confirm custom property IDs and namespace IDs before processing pending jobs.
8. Switch PHP and Nginx together. Replace **every** PHP/job worker; do not leave
   old workers behind a load balancer. Use the tested bounded job memory setting.
   Verify effective FPM configuration and actual mounted code versions, logins,
   uploads, page renders, critical Lua/semantic reports and jobs before reopening.
9. On failure, keep writes stopped. Switch to the matched patched-PHP rollback
   artifact and matching database/app/state backups; restore uploads consistently
   if writes occurred. Do not simply point old code at the migrated database.

CI retains GHCR `main-latest` and `main-YY.MM.DD` publication targets. PR checks
never publish. Actions are pinned. Main publication requires local checks and
reviewed production evidence in `build/release-gates.json`; all gates currently
remain closed until the outstanding operator validations are completed.

## Upstream references

* [PHP security release and mitigation limits](https://www.php.net/archive/2026.php#2026-10-21-1)
* [MW 1.43.11 release notes](https://raw.githubusercontent.com/wikimedia/mediawiki/1.43.11/RELEASE-NOTES-1.43)
* [SMW 7.3.1 notes](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticMediaWiki/7.3.1/docs/releasenotes/RELEASE-NOTES-7.3.1.md)
* [SMW 7 migration](https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/7.3.1/docs/migration/7.0.md)
* [SemanticScribunto 3 changes](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticScribunto/3.0.0/RELEASE-NOTES.md)
* [SRF 6 changes](https://raw.githubusercontent.com/SemanticMediaWiki/SemanticResultFormats/6.0.0/RELEASE-NOTES.md)
* [Maintained Apiunto interface](https://raw.githubusercontent.com/StarCitizenTools/mediawiki-extensions-Apiunto/v3.0.1/README.md)
* [Removed WebAuthn library advisory](https://github.com/web-auth/webauthn-framework/security/advisories/GHSA-gq4g-fpc9-vjfq)
