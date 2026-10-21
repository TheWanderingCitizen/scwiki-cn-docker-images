<?php
require_once '/var/www/mediawiki/maintenance/Maintenance.php';
class UpgradeSmoke extends Maintenance {
    public function __construct() {
        parent::__construct();
        $this->addOption('sendmail-only', 'Check the non-SMTP transport without sending mail');
    }
    public function execute() {
        if ($this->hasOption('sendmail-only')) {
            global $wgSMTP;
            $wgSMTP = false;
            $method = new ReflectionMethod(SwiftMailerHooks::class, 'getSwiftMailer');
            $method->setAccessible(true);
            $transport = $method->invoke(null);
            $this->check($transport instanceof Swift_SendmailTransport && $transport->getCommand() === ini_get('sendmail_path'), 'SwiftMailer non-SMTP transport uses PHP sendmail_path');
            return;
        }
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
        $this->write('Property:Upgrade check', '[[Has type::Text]]', $user);
        $this->write('Property:Upgrade number', '[[Has type::Number]]', $user);
        $this->write('Template:UpgradeTarget', 'target', $user);
        $nonce = bin2hex(random_bytes(4));
        $this->write('Template:UpgradeLinks', '[[Template:UpgradeTarget]] ' . $nonce, $user);
        // The site callback reads pagelinks from the preceding LinksUpdate.
        $this->write('Template:UpgradeLinks', '[[Template:UpgradeTarget]] updated ' . $nonce, $user);
        $this->write('UpgradeSmokePage', '[[Upgrade check::Passed]][[Upgrade number::7]]', $user);
        $this->write('User:UpgradeAdmin', 'SESP user property check ' . $nonce, $user);
        $lua = "return { run = function() local r = mw.smw.ask({ '[[Upgrade check::Passed]]', 'limit=10' }); return 'LUA_OK_' .. tostring(#r) end }";
        $this->write('Module:UpgradeSmoke', $lua, $user, CONTENT_MODEL_SCRIBUNTO);
        $factory = SMW\Services\ServicesFactory::getInstance();
        $store = $factory->getStore();
        $data = $store->getSemanticData(SMW\DIWikiPage::newFromTitle(Title::newFromText('UpgradeSmokePage')));
        $this->check(count($data->getPropertyValues(SMW\DIProperty::newFromUserLabel('Upgrade check'))) > 0, 'SMW semantic data persisted');
        $links = $store->getSemanticData(SMW\DIWikiPage::newFromTitle(Title::newFromText('Template:UpgradeLinks')));
        $this->check(count($links->getPropertyValues(new SMW\DIProperty('_LINKSTO'))) > 0, 'custom SESP _LINKSTO SQL callback');
        $userData = $store->getSemanticData(SMW\DIWikiPage::newFromTitle(Title::newFromText('User:UpgradeAdmin')));
        foreach (['___USERREG', '___USEREDITCNT'] as $property) {
            $this->check(count($userData->getPropertyValues(new SMW\DIProperty($property))) > 0, 'SESP ' . $property);
        }
        $parser = MediaWiki\MediaWikiServices::getInstance()->getParser();
        $options = ParserOptions::newFromUser($user);
        foreach ([
            ['{{#ask:[[Upgrade check::Passed]]|?Upgrade check|format=table}}', 'UpgradeSmokePage', 'SRF table'],
            ['{{#ask:[[Upgrade check::Passed]]|?Upgrade number|format=sum}}', '7', 'SRF sum'],
            ['{{#invoke:UpgradeSmoke|run}}', 'LUA_OK_1', 'SemanticScribunto Lua query'],
            ['<syntaxhighlight lang="php"><?php echo 7;</syntaxhighlight>', 'class="mw-highlight', 'SyntaxHighlight with pinned Pygments'],
        ] as [$wiki, $needle, $label]) {
            $html = $parser->parse($wiki, Title::newFromText('UpgradeSmokePage'), $options)->getText();
            $this->check(str_contains($html, $needle), $label . ': ' . strip_tags($html));
        }
        $key = str_repeat('k', 64);
        $jwt = Firebase\JWT\JWT::encode(['sub'=>'test'], $key, 'HS256');
        $this->check(Firebase\JWT\JWT::decode($jwt, new Firebase\JWT\Key($key, 'HS256'))->sub === 'test', 'CheckUser JWT 7 API');
        $session = MediaWiki\Session\SessionManager::getGlobalSession();
        $manager = new MediaWiki\CheckUser\TokenManager($key);
        $payload = ['targets' => ['UpgradeAdmin']];
        $this->check($manager->decode($session, $manager->encode($session, $payload)) === $payload, 'CheckUser TokenManager JWT round trip');
        $mail = new Swift_Message('Upgrade test');
        $mail->setFrom('test@example.invalid')->setTo('receiver@example.invalid')->setBody('test');
        $this->check(str_contains($mail->toString(), 'Upgrade test'), 'SwiftMailer 6 message construction');
        $process = proc_open([PHP_BINARY, '/test/smtp-server.php'], [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
        stream_set_timeout($pipes[1], 20);
        $this->check(trim(fgets($pipes[1])) === 'READY', 'local SMTP sink ready');
        global $wgSMTP, $wgSMTPAuthenticationMethod;
        $wgSMTP = ['host'=>'127.0.0.1','port'=>2525,'username'=>'','password'=>'','auth'=>false];
        $wgSMTPAuthenticationMethod = null;
        $result = SwiftMailerHooks::onAlternateUserMailer(['Return-Path'=>'sender@example.invalid'],
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
        $result = $page->doUserEditContent(ContentHandler::makeContent($text, $title, $model), $user, 'Upgrade smoke');
        $this->check($result->isOK(), 'edit ' . $name . ($result->isOK() ? '' : ': ' . $result->getWikiText()));
        DeferredUpdates::doUpdates();
    }
    private function check($ok, $label) {
        if (!$ok) { $this->fatalError($label); }
        $this->output('PASS ' . $label . "\n");
    }
}
$maintClass = UpgradeSmoke::class;
require_once RUN_MAINTENANCE_IF_MAIN;
