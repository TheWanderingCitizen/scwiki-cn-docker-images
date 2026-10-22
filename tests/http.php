<?php
require '/var/www/mediawiki/includes/AutoLoader.php';
$base = getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8080';
$probe = json_decode(file_get_contents($base . '/upgrade-fpm-probe.php'), true, 512, JSON_THROW_ON_ERROR);
if ($probe['php'] !== '8.3.36' || $probe['uid'] !== 33 || $probe['uploadProgress'] || $probe['sapi'] !== 'fpm-fcgi') {
    throw new RuntimeException('Effective FPM security configuration failed: ' . json_encode($probe));
}
echo "PASS effective FPM PHP version / www-data / upload-progress configuration\n";
$stream = GuzzleHttp\Psr7\Utils::streamFor('stream-ok');
if (!$stream instanceof Psr\Http\Message\StreamInterface || $stream->read(6) !== 'stream') {
    throw new RuntimeException('PSR stream contract failed');
}
$stream->rewind();
if ((string)$stream !== 'stream-ok') { throw new RuntimeException('PSR stream rewind failed'); }
echo "PASS PSR stream interface\n";
$yaml = ['upgrade' => ['enabled' => true, 'version' => '1.43.11']];
if (Symfony\Component\Yaml\Yaml::parse(Symfony\Component\Yaml\Yaml::dump($yaml)) !== $yaml) {
    throw new RuntimeException('YAML round trip failed');
}
echo "PASS YAML round trip\n";
$client = new GuzzleHttp\Client(['base_uri' => $base, 'timeout' => 30]);
$uri = '/api.php?action=query&meta=siteinfo&format=json';
foreach ([$client->get($uri), $client->getAsync($uri)->wait()] as $response) {
    $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    if (!str_contains($data['query']['general']['generator'] ?? '', '1.43.11')) {
        throw new RuntimeException('Guzzle siteinfo request failed');
    }
}
echo "PASS Guzzle synchronous and asynchronous HTTP\n";
$redirect = $client->get('/upgrade-fpm-probe.php?redirect=1');
if (json_decode((string)$redirect->getBody(), true)['php'] !== '8.3.36') {
    throw new RuntimeException('Guzzle redirect response was corrupted');
}
echo "PASS Guzzle redirect handling\n";
$cookie = tempnam('/tmp', 'wiki-cookie');
function api(array $params, bool $post = false): array {
    global $cookie, $base;
    $params['format'] = 'json';
    $curl = curl_init($base . '/api.php' . ($post ? '' : '?' . http_build_query($params)));
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_COOKIEJAR=>$cookie, CURLOPT_COOKIEFILE=>$cookie]);
    if ($post) { curl_setopt($curl, CURLOPT_POSTFIELDS, isset($params['file']) ? $params : http_build_query($params)); }
    $body = curl_exec($curl);
    if (curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) { throw new RuntimeException((string)$body); }
    return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
}
$token = api(['action'=>'query','meta'=>'tokens','type'=>'login'])['query']['tokens']['logintoken'];
$login = api(['action'=>'login','lgname'=>'UpgradeAdmin','lgpassword'=>'Local-test-password-2026!','lgtoken'=>$token], true);
if (($login['login']['result'] ?? '') !== 'Success') { throw new RuntimeException(json_encode($login)); }
echo "PASS HTTP local login\n";
$token = api(['action'=>'query','meta'=>'tokens'])['query']['tokens']['csrftoken'];
$edit = api(['action'=>'edit','title'=>'HTTPUpgradeCheck','text'=>'HTTP_OK','token'=>$token], true);
if (($edit['edit']['result'] ?? '') !== 'Success') { throw new RuntimeException(json_encode($edit)); }
echo "PASS HTTP API edit\n";
$png = new Imagick();
$png->newImage(3, 3, '#' . bin2hex(random_bytes(3)));
$png->setImageFormat('png');
$uploadPath = '/tmp/security-recovery-upload.png';
$png->writeImage($uploadPath);
$upload = api(['action'=>'upload', 'filename'=>'SecurityRecovery-' . bin2hex(random_bytes(4)) . '.png', 'ignorewarnings'=>'1',
    'token'=>$token, 'file'=>new CURLFile($uploadPath, 'image/png', 'SecurityRecovery.png')], true);
if (($upload['upload']['result'] ?? '') !== 'Success') { throw new RuntimeException(json_encode($upload)); }
echo "PASS HTTP authenticated image upload\n";
$html = file_get_contents($base . '/index.php?title=HTTPUpgradeCheck');
if (!str_contains($html, 'HTTP_OK') || !str_contains($html, 'skin-citizen')) { throw new RuntimeException('Page / Citizen rendering failed'); }
echo "PASS HTTP page and Citizen rendering\n";
