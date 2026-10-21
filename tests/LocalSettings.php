<?php
// Exercise the production configuration against disposable services only.
$defaults = [
    'SiteName' => 'Upgrade smoke', 'Server' => 'http://127.0.0.1:8080',
    'SecretKey' => str_repeat('a', 64), 'UpgradeKey' => 'local-upgrade-test',
    'DbType' => 'mysql', 'DbServer' => 'db', 'DbName' => 'wiki',
    'DbUser' => 'wiki', 'DbPassword' => 'local-test-password',
    'RedisAddress' => 'redis:6379', 'RedisPassword' => null,
    'S3Key' => 'test', 'S3Secret' => 'test', 'S3BucketName' => 'test',
    'S3BucketDomain' => 'example.invalid', 'S3Endpoint' => 'https://example.invalid', 'S3Region' => 'us-east-1',
    'OpenIDConnectProviderUrl' => 'https://example.invalid', 'OpenIDConnectClientID' => 'test', 'OpenIDConnectClientSecret' => 'test',
    'wgMultiPurgeCloudFlareApiToken' => '', 'wgMultiPurgeCloudFlareZoneId' => '',
];
$_ENV = array_merge($_ENV, $defaults);
$settings = file_get_contents('/production-settings.php');
// External service behavior is explicitly outside this local smoke test.
if (!getenv('TEST_FULL_EXTENSION_BOOT')) {
    $settings = preg_replace("/^wfLoadExtension\( '(AWS|Discord|MultiPurge)' \);/m", '', $settings);
}
eval('?>' . $settings);
unset($wgFileBackends['s3']);
$wgForceHTTPS = false;
$wgCookieSecure = false;
$wgUseCdn = false;
$wgUseFileCache = false;
$wgEnableEmail = false;
$wgArticlePath = '/index.php?title=$1';
$wgSitename = 'Upgrade smoke';
$wgJobRunRate = 0;
$wgShowExceptionDetails = true;
$wgGroupPermissions['sysop']['edit'] = true;
$wgGroupPermissions['sysop']['createpage'] = true;
$wgGroupPermissions['sysop']['createtalk'] = true;
$wgSMTP = ['host'=>'127.0.0.1', 'port'=>2525, 'username'=>'', 'password'=>'', 'auth'=>false];
$wgSMTPAuthenticationMethod = null;
$wgSMTPTlsPeerVerification = false; // Upstream's inverted flag: false retains peer verification.
$wgApiuntoSources['Smoke'] = [
    'baseUrl'=>'http://127.0.0.1:8099/api/', 'timeout'=>2,
    'cacheDuration'=>5, 'followRedirects'=>true,
];
$wgRecoveryApiuntoSource = 'Smoke';
