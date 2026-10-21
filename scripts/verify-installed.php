<?php
// Run in the complete image, not the source or PHP-only stage.
$root = '/var/www/mediawiki';
require "$root/vendor/autoload.php";
$lock = json_decode(file_get_contents("$root/composer.lock"), true, 512, JSON_THROW_ON_ERROR);
$installed = json_decode(file_get_contents("$root/vendor/composer/installed.json"), true, 512, JSON_THROW_ON_ERROR)['packages'];
$expected = array_column($lock['packages'], null, 'name');
$actual = array_column($installed, null, 'name');
if (array_diff_key($expected, $actual) || array_diff_key($actual, $expected)) {
    throw new RuntimeException('Installed package set differs from production lock');
}
foreach ($expected as $name=>$package) {
    foreach (['version', 'source', 'dist'] as $key) {
        if (($package[$key] ?? null) !== ($actual[$name][$key] ?? null)) {
            throw new RuntimeException("Installed $name $key differs from lock");
        }
    }
    if (in_array($package['type'], ['mediawiki-extension', 'mediawiki-skin'], true)
        && (!preg_match('/^[a-f0-9]{40}$/', $package['source']['reference'])
        || $package['source']['reference'] !== $package['dist']['reference'])) {
        throw new RuntimeException("Unpinned extension $name");
    }
}
$rootManifest = json_decode(file_get_contents("$root/composer.json"), true, 512, JSON_THROW_ON_ERROR);
$settings = file_get_contents("$root/LocalSettings.php");
preg_match_all("/^wfLoad(Extension|Skin)\\(\\s*'([^']+)'\\s*\\);/m", $settings, $loads, PREG_SET_ORDER);
foreach ($loads as $load) {
    $directory = $load[1] === 'Skin' ? 'skins' : 'extensions';
    $manifestFile = $load[1] === 'Skin' ? 'skin.json' : 'extension.json';
    if (!is_file("$root/$directory/{$load[2]}/$manifestFile")) {
        throw new RuntimeException("Configured {$load[1]} {$load[2]} has no installed manifest");
    }
}
$overrides = ['guzzlehttp/guzzle', 'guzzlehttp/psr7', 'guzzlehttp/promises', 'psr/http-message', 'symfony/yaml', 'aws/aws-sdk-php'];
$manifests = [];
foreach (['extensions', 'skins'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/$directory", FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $relative = substr($file->getPathname(), strlen($root) + 1);
        if ($file->getFilename() !== 'composer.json' || preg_match('~/(vendor|node_modules|tests|test|fixtures)/~i', $relative)) { continue; }
        $manifest = json_decode(file_get_contents($file->getPathname()), true, 512, JSON_THROW_ON_ERROR);
        if (isset($manifest['name']) && !isset($actual[$manifest['name']])
            && substr_count($relative, '/') === 2) {
            // Unmanaged bundled extensions can be present without a Composer package.
            foreach ($installed as $package) {
                $path = realpath("$root/vendor/composer/" . $package['install-path']);
                if ($path === $file->getPath() && $package['name'] !== $manifest['name']) {
                    throw new RuntimeException("Noncanonical package identity for $relative");
                }
            }
        }
        // All actual runtime manifests, including nested merge inputs, are checked.
        $manifests[] = $relative;
        foreach ($manifest['require'] ?? [] as $name=>$constraint) {
            if ($name === 'php' || str_starts_with($name, 'ext-') || str_starts_with($name, 'lib-') || str_starts_with($name, 'composer-')) { continue; }
            $package = $actual[$name] ?? null;
            if (!$package && isset($rootManifest['replace'][$name])) { continue; }
            if (!$package) { throw new RuntimeException("Missing $relative dependency $name"); }
            if (!in_array($name, $overrides, true) && !Composer\Semver\Semver::satisfies($package['version'], $constraint)) {
                throw new RuntimeException("Unsatisfied $relative: $name $constraint (installed {$package['version']})");
            }
        }
    }
}
echo 'PASS ' . count($actual) . ' locked package versions/references and ' . count($manifests) . " runtime manifests\n";
