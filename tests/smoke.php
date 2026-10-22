<?php
require_once '/var/www/mediawiki/maintenance/Maintenance.php';
class UpgradeSmoke extends Maintenance {
    public function __construct() {
        parent::__construct();
    }
    public function execute() {
        $this->check(PHP_VERSION === '8.3.36', 'PHP2Shell patched runtime');
        $this->check(!ini_get('session.upload_progress.enabled'), 'upload progress disabled');
        foreach (['apcu','redis','luasandbox','wikidiff2','imagick','intl','mysqli'] as $ext) {
            $this->check(extension_loaded($ext), $ext . ' loaded');
        }
        $image = new Imagick();
        $image->newImage(2, 2, 'white');
        $image->setImageFormat('webp');
        $this->check(substr($image->getImageBlob(), 0, 4) === 'RIFF', 'WebP conversion');
        $user = User::newFromName('UpgradeAdmin');
        $oathUser = MediaWiki\Extension\OATHAuth\OATHAuthServices::getInstance()->getUserRepository()->findByUser($user);
        $services = MediaWiki\MediaWikiServices::getInstance();
        $stash = $services->getMainObjectStash();
        if ($stash instanceof Wikimedia\ObjectCache\EmptyBagOStuff) {
            $stash = $services->getObjectCacheFactory()->getLocalServerInstance(CACHE_ANYTHING);
        }
        // Reset only this disposable account's anti-replay cache between test runs.
        $stash->delete($stash->makeKey('oathauth-totp', 'usedtokens', $oathUser->getCentralId()));
        $totp = MediaWiki\Extension\OATHAuth\Key\TOTPKey::newFromRandom();
        $windows = jakobo\HOTP\HOTP::generateByTimeWindow(Base32\Base32::decode($totp->getSecret()), 30, 0, 0);
        $otp = reset($windows)->toHOTP(6);
        $this->check($totp->verify(['token'=>$otp], $oathUser), 'OATHAuth TOTP verification with disposable key');
        $this->check(!$totp->verify(['token'=>$otp], $oathUser), 'OATHAuth rejects replayed TOTP');
        $this->check($totp->isScratchToken($totp->getScratchTokens()[0]), 'OATHAuth generated recovery code recognition');
        $this->write('Property:Upgrade check', '[[Has type::Text]]', $user);
        $this->write('Property:Upgrade number', '[[Has type::Number]]', $user);
        $this->write('Template:UpgradeTarget', 'target', $user);
        $this->write('Module:UpgradeLinks', 'return {}', $user, CONTENT_MODEL_SCRIBUNTO);
        $nonce = bin2hex(random_bytes(4));
        $linkText = '[[Template:UpgradeTarget]][[Template:UpgradeLinks]][[Module:UpgradeLinks]][[UpgradeSmokePage]][[Template:MissingRecoveryTarget]] ';
        $this->write('Template:UpgradeLinks', $linkText . $nonce, $user);
        // The site callback reads pagelinks from the preceding LinksUpdate.
        $this->write('Template:UpgradeLinks', $linkText . 'updated ' . $nonce, $user);
        $this->write('UpgradeSmokePage', '[[Upgrade check::Passed]][[Upgrade number::7]][[Category:UpgradeSmoke]]', $user);
        $this->write('User:UpgradeAdmin', 'SESP user property check ' . $nonce, $user);
        $lua = "return { run = function() local r = mw.smw.ask({ '[[Upgrade check::Passed]]', 'limit=10' }); return 'LUA_OK_' .. tostring(#r) end }";
        $this->write('Module:UpgradeSmoke', $lua, $user, CONTENT_MODEL_SCRIBUNTO);
        $apiProcess = proc_open([PHP_BINARY, '-S', '127.0.0.1:8099', '/test/api-router.php'],
            [0=>['pipe','r'],1=>['file','/tmp/api-fixture.log','a'],2=>['file','/tmp/api-fixture.log','a']], $apiPipes);
        try {
            $ready = false;
            for ($attempt=0; $attempt<50; $attempt++) {
                $socket = @fsockopen('127.0.0.1', 8099);
                if ($socket) { fclose($socket); $ready = true; break; }
                usleep(100000);
            }
            $this->check($ready, 'Apiunto local fixture ready');
            $apiLua = <<<'LUA'
return { run = function()
    local raw = mw.ext.Apiunto.fetch('Smoke', 'echo', {locale='zh_CN', fields={'a','b'}})
    local result = mw.text.jsonDecode(raw)
    return 'API_OK_' .. result.query.locale .. '_' .. result.query.fields .. '_' .. result.path
end }
LUA;
            $this->write('Module:UpgradeApiunto', $apiLua, $user, CONTENT_MODEL_SCRIBUNTO);
            $html = MediaWiki\MediaWikiServices::getInstance()->getParser()->parse(
                '{{#invoke:UpgradeApiunto|run}}', Title::newFromText('UpgradeApiTest'), ParserOptions::newFromUser($user))->getText();
            $this->check(str_contains(html_entity_decode($html), 'API_OK_zh_CN_a,b_/api/echo'), 'Apiunto Lua arguments, locale and response: ' . strip_tags($html));
            $legacyLua = <<<'LUA'
return { run = function()
    local a = mw.ext.Apiunto
    local x = mw.text.jsonDecode(a.get_raw('echo', {include={'hardpoints','parts'}, ignored='drop', limit=1000}))
    assert(x.query.locale == 'zh_CN' and x.query.include == 'hardpoints,parts' and x.query.ignored == nil)
    local y = mw.text.jsonDecode(a.get_ship('ROC', {locale={'en_US','zh_CN'}}))
    assert(y.path == '/api/v2/vehicles/ROC' and y.query.locale == 'en_US,zh_CN')
    return 'LEGACY_API_OK'
end }
LUA;
            $this->write('Module:UpgradeLegacyApiunto', $legacyLua, $user, CONTENT_MODEL_SCRIBUNTO);
            $html = MediaWiki\MediaWikiServices::getInstance()->getParser()->parse(
                '{{#invoke:UpgradeLegacyApiunto|run}}', Title::newFromText('UpgradeLegacyApiTest'), ParserOptions::newFromUser($user))->getText();
            $this->check(str_contains($html, 'LEGACY_API_OK'), 'deployed Apiunto call/locale/include contract: ' . strip_tags($html));
        } finally {
            proc_terminate($apiProcess);
            proc_close($apiProcess);
        }
        $batch = '';
        for ($index=1; $index<=501; $index++) {
            $batch .= '{{#subobject:row' . $index . '|Upgrade check=Batch|Upgrade number=' . $index . '}}';
        }
        $this->write('UpgradeBatch', $batch, $user);
        $batchLua = <<<'LUA'
return { run = function()
    local rows = mw.smw.ask({'[[Upgrade check::Batch]]','?Upgrade number','sort=Upgrade number','order=asc','limit=1000'})
    assert(#rows == 501, 'Expected 501, got '..tostring(#rows))
    local seen = {}
    for _, row in ipairs(rows) do
        local key = tostring(row['Upgrade number'])
        assert(not seen[key], 'Duplicate '..key); seen[key] = true
    end
    return 'BATCH_501_OK'
end }
LUA;
        $this->write('Module:UpgradeBatch', $batchLua, $user, CONTENT_MODEL_SCRIBUNTO);
        $factory = SMW\Services\ServicesFactory::getInstance();
        $store = $factory->getStore();
        $data = $store->getSemanticData(SMW\DataItems\WikiPage::newFromTitle(Title::newFromText('UpgradeSmokePage')));
        $this->check(count($data->getPropertyValues(SMW\DataItems\Property::newFromUserLabel('Upgrade check'))) > 0, 'SMW semantic data persisted');
        $links = $store->getSemanticData(SMW\DataItems\WikiPage::newFromTitle(Title::newFromText('Template:UpgradeLinks')));
        $linkValues = $links->getPropertyValues(new SMW\DataItems\Property('_LINKSTO'));
        $this->check(count($linkValues) === 1 && reset($linkValues)->getTitle()->getPrefixedDBkey() === 'Template:UpgradeTarget', 'custom _LINKSTO identity, namespace, self-name and missing-target filtering');
        $nativeAbsent = false;
        try { new SMW\DataItems\Property('___LINKSTO'); }
        catch (SMW\Exception\PredefinedPropertyLabelMismatchException $exception) { $nativeAbsent = true; }
        $this->check($nativeAbsent, 'SESP native _LINKSTO cannot shadow custom property');
        $userData = $store->getSemanticData(SMW\DataItems\WikiPage::newFromTitle(Title::newFromText('User:UpgradeAdmin')));
        foreach (['___USERREG', '___USEREDITCNT'] as $property) {
            $this->check(count($userData->getPropertyValues(new SMW\DataItems\Property($property))) > 0, 'SESP ' . $property);
        }
        $parser = MediaWiki\MediaWikiServices::getInstance()->getParser();
        $options = ParserOptions::newFromUser($user);
        foreach ([
            ['{{#ask:[[Upgrade check::Passed]]|?Upgrade check|format=table}}', 'UpgradeSmokePage', 'SRF table'],
            ['{{#ask:[[Upgrade check::Passed]]|?Upgrade number|format=sum}}', '7', 'SRF sum'],
            ['{{#invoke:UpgradeSmoke|run}}', 'LUA_OK_1', 'SemanticScribunto Lua query'],
            ['{{#invoke:UpgradeBatch|run}}', 'BATCH_501_OK', 'bounded 1000-row compatibility across 500-row boundary'],
            ["<dpl>\ncategory=UpgradeSmoke\ncount=5\n</dpl>", 'UpgradeSmokePage', 'DPL category query'],
            ['{{#ev:youtube|dQw4w9WgXcQ}}', 'youtube', 'EmbedVideo rendering'],
            ['<syntaxhighlight lang="php"><?php echo 7;</syntaxhighlight>', 'class="mw-highlight', 'SyntaxHighlight with pinned Pygments'],
        ] as [$wiki, $needle, $label]) {
            $html = $parser->parse($wiki, Title::newFromText('UpgradeParserTest'), $options)->getText();
            $this->check(str_contains($html, $needle), $label . ': ' . substr(trim(strip_tags($html)), 0, 100));
        }
        $key = str_repeat('k', 64);
        $jwt = Firebase\JWT\JWT::encode(['sub'=>'test'], $key, 'HS256');
        $this->check(Firebase\JWT\JWT::decode($jwt, new Firebase\JWT\Key($key, 'HS256'))->sub === 'test', 'CheckUser JWT 7 API');
        $session = MediaWiki\Session\SessionManager::getGlobalSession();
        $manager = new MediaWiki\CheckUser\Services\TokenManager($key);
        $payload = ['targets' => ['UpgradeAdmin']];
        $this->check($manager->decode($session, $manager->encode($session, $payload)) === $payload, 'CheckUser TokenManager JWT round trip');
        $process = proc_open([PHP_BINARY, '/test/smtp-server.php'], [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
        stream_set_timeout($pipes[1], 20);
        $this->check(trim(fgets($pipes[1])) === 'READY', 'local SMTP sink ready');
        global $wgSMTP, $wgSMTPAuthenticationMethod;
        $wgSMTP = ['host'=>'127.0.0.1','port'=>2525,'username'=>'','password'=>'','auth'=>false];
        $wgSMTPAuthenticationMethod = null;
        $result = (new MediaWiki\Extension\SwiftMailer\MailHooks())->onAlternateUserMailer(['Return-Path'=>'sender@example.invalid'],
            [new MailAddress('receiver@example.invalid')], new MailAddress('sender@example.invalid'), 'Upgrade test', 'SMTP_LOCAL_OK');
        $received = json_decode(stream_get_contents($pipes[1]), true, 512, JSON_THROW_ON_ERROR);
        $this->check(proc_close($process) === 0 && $result === false && str_contains($received['message'], 'SMTP_LOCAL_OK'), 'SwiftMailer extension actual SMTP delivery to local sink');
        $s3 = new Aws\S3\S3Client(['region'=>'us-east-1','version'=>'2006-03-01','credentials'=>['key'=>'test','secret'=>'test']]);
        $signed = $s3->createPresignedRequest($s3->getCommand('GetObject',['Bucket'=>'test-bucket','Key'=>'test.txt']), '+5 minutes');
        $this->check(str_contains((string)$signed->getUri(),'X-Amz-Signature='), 'S3 offline signing');
        $this->output("ALL BUSINESS SMOKE CHECKS PASSED\n");
    }
    private function write($name, $text, $user, $model = CONTENT_MODEL_WIKITEXT) {
        $title = Title::newFromText($name);
        $page = MediaWiki\MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle($title);
        $updater = $page->newPageUpdater($user);
        $updater->setContent('main', ContentHandler::makeContent($text, $title, $model));
        $updater->saveRevision(CommentStoreComment::newUnsavedComment('Upgrade smoke'));
        $this->check($updater->wasSuccessful(), 'edit ' . $name);
        DeferredUpdates::doUpdates();
    }
    private function check($ok, $label) {
        if (!$ok) { $this->fatalError($label); }
        $this->output('PASS ' . $label . "\n");
    }
}
$maintClass = UpgradeSmoke::class;
require_once RUN_MAINTENANCE_IF_MAIN;
