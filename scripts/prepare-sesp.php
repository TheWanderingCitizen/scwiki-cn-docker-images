<?php
// Preserve the site's historical _LINKSTO property; SESP's native ID differs.
$root = '/var/www/mediawiki';
$definitions = json_decode(file_get_contents("$root/extensions/SemanticExtraSpecialProperties/data/definitions.json"), true, 512, JSON_THROW_ON_ERROR);
if (($definitions['_LINKSTO']['id'] ?? null) !== '___LINKSTO') {
    throw new RuntimeException('SESP native _LINKSTO definition changed; review compatibility');
}
unset($definitions['_LINKSTO']);
mkdir("$root/recovery", 0755, true);
file_put_contents("$root/recovery/sesp-definitions.json", json_encode($definitions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
