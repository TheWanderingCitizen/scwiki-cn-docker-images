<?php
$root = '/workspace';
$report = json_decode(file_get_contents("$root/build/audit.json"), true, 512, JSON_THROW_ON_ERROR);
$lock = json_decode(file_get_contents("$root/build/composer.lock"), true, 512, JSON_THROW_ON_ERROR);
$cases = ['approved' => [$report, $lock, true]];
$unknown = $report;
$unknown['advisories']['mediawiki/semantic-media-wiki'][0]['advisoryId'] = 'PKSA-unapproved-test';
$cases['unknown advisory'] = [$unknown, $lock, false];
$wrongPackage = $report;
$wrongPackage['advisories']['starcitizenwiki/embedvideo'][] = $report['advisories']['mediawiki/semantic-media-wiki'][0];
$cases['wrong package'] = [$wrongPackage, $lock, false];
$wrongVersion = $lock;
foreach ($wrongVersion['packages'] as &$package) {
    if ($package['name'] === 'mediawiki/semantic-media-wiki') { $package['version'] = '4.1.0'; }
}
unset($package);
$cases['wrong version'] = [$report, $wrongVersion, false];
$cases['incomplete response'] = [[], $lock, false];
$cases['unexpectedly empty feed'] = [['advisories'=>[], 'abandoned'=>[]], $lock, false];
foreach ($cases as $name => [$audit, $packages, $expected]) {
    $auditFile = tempnam('/tmp', 'audit-gate-');
    $lockFile = tempnam('/tmp', 'lock-gate-');
    file_put_contents($auditFile, json_encode($audit));
    file_put_contents($lockFile, json_encode($packages));
    $process = proc_open([PHP_BINARY, "$root/scripts/check-audit.php", $auditFile, "$root/build/composer.json", $lockFile],
        [0=>['pipe','r'], 1=>['pipe','w'], 2=>['pipe','w']], $pipes);
    stream_get_contents($pipes[1]);
    stream_get_contents($pipes[2]);
    $status = proc_close($process);
    unlink($auditFile);
    unlink($lockFile);
    if (($status === 0) !== $expected) { throw new RuntimeException("Audit gate failed case: $name"); }
}
echo "PASS audit gate allows reviewed IDs and rejects unknown IDs / wrong package / wrong version / incomplete response\n";
