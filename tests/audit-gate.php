<?php
// Unknown IDs, truncated responses, malformed types and filtering must fail closed.
$cases = [
    ['advisories'=>[], 'abandoned'=>[], 'filter'=>[]],
    ['advisories'=>['new/package'=>[['advisoryId'=>'UNKNOWN-NEW-ID']]], 'abandoned'=>[], 'filter'=>[]],
    ['advisories'=>[], 'abandoned'=>[]],
    ['abandoned'=>[]],
    ['advisories'=>null, 'abandoned'=>[]],
    ['advisories'=>[], 'abandoned'=>[], 'filter'=>['packages'=>['safe/package']]],
    ['advisories'=>[], 'abandoned'=>[], 'ignored-advisories'=>['UNKNOWN-NEW-ID']],
];
foreach ($cases as $index=>$case) {
    $file = tempnam(sys_get_temp_dir(), 'audit-negative');
    file_put_contents($file, json_encode($case));
    exec(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/../scripts/check-audit.php') . ' ' . escapeshellarg($file) . ' 2>&1', $output, $status);
    unlink($file);
    if (($status === 0) !== ($index === 0)) { throw new RuntimeException('Audit gate failed case ' . $index); }
}
$file = tempnam(sys_get_temp_dir(), 'audit-truncated');
file_put_contents($file, '{"advisories":');
exec(PHP_BINARY . ' ' . escapeshellarg(__DIR__ . '/../scripts/check-audit.php') . ' ' . escapeshellarg($file) . ' 2>&1', $output, $status);
unlink($file);
if ($status === 0) { throw new RuntimeException('Truncated audit accepted'); }
echo "PASS audit gate negative cases\n";
