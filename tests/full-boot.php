<?php
// Load every configured extension with fake integration settings. No external login.
require_once '/var/www/mediawiki/maintenance/Maintenance.php';
class FullConfigurationBoot extends Maintenance {
    public function execute() {
        $registry = MediaWiki\Registration\ExtensionRegistry::getInstance();
        foreach (['OpenID Connect', 'PluggableAuth', 'AWS', 'Discord', 'MultiPurge'] as $name) {
            if (!$registry->isLoaded($name)) { throw new RuntimeException('Extension not loaded: ' . $name); }
        }
        $this->output("PASS all configured external-integration extensions boot with placeholder credentials\n");
    }
}
$maintClass = FullConfigurationBoot::class;
require_once RUN_MAINTENANCE_IF_MAIN;
