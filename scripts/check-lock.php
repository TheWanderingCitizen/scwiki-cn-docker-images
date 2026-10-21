<?php
require '/var/www/mediawiki/vendor/composer/InstalledVersions.php';
$root = '/var/www/mediawiki';
$lock = json_decode(file_get_contents("$root/composer.lock"), true, 512, JSON_THROW_ON_ERROR);
$manifest = json_decode(file_get_contents("$root/composer.json"), true, 512, JSON_THROW_ON_ERROR);
foreach ($lock['packages'] as $package) {
    $name = $package['name'];
    $reference = $package['source']['reference'] ?? $package['dist']['reference'] ?? null;
    if (!Composer\InstalledVersions::isInstalled($name)
        || Composer\InstalledVersions::getPrettyVersion($name) !== $package['version']
        || Composer\InstalledVersions::getReference($name) !== $reference) {
        throw new RuntimeException("Installed version/reference differs from lock: $name");
    }
}
foreach ($manifest['repositories'] as $repository) {
    $package = $repository['package'];
    $sha = $package['source']['reference'];
    if (!preg_match('/^[a-f0-9]{40}$/', $sha) || $sha !== $package['dist']['reference']
        || !str_ends_with($package['dist']['url'], $sha)) {
        throw new RuntimeException('Unpinned or inconsistent extension source: ' . $package['name']);
    }
}
echo 'PASS ' . count($lock['packages']) . " installed production versions and references match lock\n";
foreach (json_decode(file_get_contents('/workspace/build/bundled-extensions.json'), true, 512, JSON_THROW_ON_ERROR) as $extension) {
    $data = json_decode(file_get_contents($root . '/' . $extension['path']), true, 512, JSON_THROW_ON_ERROR);
    if (($data['version'] ?? 'bundled') !== $extension['version']) {
        throw new RuntimeException('Bundled extension version differs: ' . $extension['name']);
    }
}
echo "PASS bundled extension and skin versions match release inventory\n";
