<?php
// Core release extensions not separately replaced by the production manifest.
$root = '/var/www/mediawiki';
$manifest = json_decode(file_get_contents("$root/composer.json"), true, 512, JSON_THROW_ON_ERROR);
$managed = [];
foreach ($manifest['repositories'] as $repository) {
    $package = $repository['package'];
    $managed[$package['extra']['installer-name']] = true;
}
$result = [];
foreach (array_merge(glob("$root/extensions/*/extension.json"), glob("$root/skins/*/skin.json")) as $file) {
    if (isset($managed[basename(dirname($file))])) { continue; }
    $data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    $result[] = ['path'=>substr($file, strlen($root) + 1), 'name'=>$data['name'], 'version'=>$data['version'] ?? 'bundled', 'requires'=>$data['requires'] ?? []];
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
