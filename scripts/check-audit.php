<?php
// Verify every advisory against an exact ID/package/version allowlist.
$report = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
$manifest = json_decode(file_get_contents($argv[2]), true, 512, JSON_THROW_ON_ERROR);
$allowed = $manifest['config']['policy']['advisories']['ignore-id'] ?? [];
$expected = [
    'mediawiki/semantic-media-wiki' => '4.2.0',
    'starcitizenwiki/embedvideo' => '3.4.3',
    'universal-omega/dynamic-page-list3' => '3.5.2',
    'mediawiki/webauthn' => '1.39.0',
];
$lock = json_decode(file_get_contents($argv[3]), true, 512, JSON_THROW_ON_ERROR);
$versions = array_column($lock['packages'], 'version', 'name');
$failures = [];
if (!array_key_exists('advisories', $report) || !array_key_exists('abandoned', $report)) {
    throw new RuntimeException('Incomplete Composer audit response');
}
$packageIds = [
    'mediawiki/semantic-media-wiki' => ['PKSA-8k3f-637m-ptt7', 'PKSA-rt5h-3dwq-vdnx', 'PKSA-5kcn-7vbr-1pdw', 'PKSA-k3v8-z9j2-2f38', 'PKSA-stqx-crkb-215f', 'PKSA-2trz-n7bz-xg2h', 'PKSA-6xyn-p8dg-1kgj', 'PKSA-q8ty-xz99-jhhj'],
    'starcitizenwiki/embedvideo' => ['PKSA-1f7b-5f9b-ggt9', 'PKSA-wg3k-7dyt-r1n5', 'PKSA-17vj-28c7-d53v', 'PKSA-bvqp-6135-khxc', 'PKSA-fnny-x6d4-t1qw'],
    'universal-omega/dynamic-page-list3' => ['PKSA-5fdy-91p7-w18s'],
    'mediawiki/webauthn' => ['PKSA-cncr-q695-v65g'],
];
$registered = array_merge(...array_values($packageIds));
if (array_diff(array_keys($allowed), $registered) || array_diff($registered, array_keys($allowed))) {
    throw new RuntimeException('Manifest security exceptions differ from the reviewed allowlist');
}
foreach ($allowed as $options) {
    if (($options['on-audit'] ?? true) !== false || ($options['on-block'] ?? false) !== true) {
        throw new RuntimeException('Exceptions must allow resolution and remain visible to audit');
    }
}
$seen = [];
foreach ($report['advisories'] ?? [] as $package => $advisories) {
    foreach ($advisories as $advisory) {
        $id = $advisory['advisoryId'];
        $seen[] = $id;
        if (!isset($allowed[$id], $expected[$package]) || !in_array($id, $packageIds[$package], true) || ltrim($versions[$package] ?? '', 'v') !== $expected[$package]) {
            $failures[] = "$package: $id";
        }
    }
}
// These exact locked versions remain affected. An unexpectedly empty/partial
// feed must be reviewed, rather than being mistaken for a clean security audit.
$knownVisible = array_merge($packageIds['mediawiki/semantic-media-wiki'], $packageIds['starcitizenwiki/embedvideo'], $packageIds['universal-omega/dynamic-page-list3']);
if (array_diff($knownVisible, $seen)) {
    $failures[] = 'Known affected advisories disappeared; verify the audit feed or reviewed withdrawals.';
}
if ($failures) {
    fwrite(STDERR, "Unapproved security advisories:\n" . implode("\n", $failures) . "\n");
    exit(1);
}
echo "Audit contains only explicitly approved package/version exceptions.\n";
