<?php
// Local upstream fixture: no requests leave the disposable wiki container.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/api/redirect') {
    header('Location: /api/echo?' . http_build_query($_GET), true, 302);
    exit;
}
if ($path === '/api/cross-origin') {
    header('Location: http://example.invalid/private', true, 302);
    exit;
}
header('Content-Type: application/json');
echo json_encode(['path'=>$path, 'query'=>$_GET]);
