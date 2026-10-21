<?php
$report = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
if (!is_array($report) || !isset($report['advisories'], $report['abandoned'], $report['filter'])
    || !is_array($report['advisories']) || !is_array($report['abandoned'])
    || !is_array($report['filter'])
    || ($report['filter'] ?? []) || ($report['ignored-advisories'] ?? [])) {
    throw new RuntimeException('Incomplete or filtered security audit');
}
if (count($report['advisories'])) {
    throw new RuntimeException('Security advisories block delivery: ' . implode(', ', array_keys($report['advisories'])));
}
echo "PASS zero reported security advisories; abandoned packages remain in the report\n";
