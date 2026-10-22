<?php
// Copied only into the disposable test volume; never included in the image.
if (isset($_GET['redirect'])) {
    header('Location: /upgrade-fpm-probe.php');
    exit;
}
header('Content-Type: application/json');
echo json_encode([
    'php' => PHP_VERSION,
    'uid' => posix_geteuid(),
    'sapi' => PHP_SAPI,
    'uploadProgress' => (bool)ini_get('session.upload_progress.enabled'),
]);
