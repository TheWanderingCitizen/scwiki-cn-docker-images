<?php
// This script requires a restored snapshot; never run against a live database.
require_once '/var/www/mediawiki/maintenance/Maintenance.php';
class ProductionSnapshotSmoke extends Maintenance {
    public function execute() {
        $services = MediaWiki\MediaWikiServices::getInstance();
        $user = $services->getUserFactory()->newFromName('UpgradeAdmin');
        $title = MediaWiki\Title\Title::newFromText('Module:UpgradeProductionCompat');
        $page = $services->getWikiPageFactory()->newFromTitle($title);
        $lua = <<<'LUA'
return { run = function()
    assert(type(mw.ext.Apiunto.get_raw) == 'function')
    local hardpoints = require('Module:VehicleHardpoint')
    assert(type(hardpoints) == 'table')
    local dependency = require('Module:DependencyList')
    local output = dependency._main('Module:VehicleHardpoint', 'no', 'yes')
    assert(type(output) == 'string' and #output > 0)
    return 'PRODUCTION_MODULES_OK:' .. output
end }
LUA;
        $updater = $page->newPageUpdater($user);
        $updater->setContent('main', ContentHandler::makeContent($lua, $title, CONTENT_MODEL_SCRIBUNTO));
        $updater->saveRevision(CommentStoreComment::newUnsavedComment('Isolated production compatibility check'));
        if (!$updater->wasSuccessful()) { throw new RuntimeException('Cannot write isolated wrapper'); }
        DeferredUpdates::doUpdates();
        $output = $services->getParser()->parse('{{#invoke:UpgradeProductionCompat|run}}',
            MediaWiki\Title\Title::newFromText('ProductionCompatTest'), ParserOptions::newFromUser($user))->getText();
        if (!str_contains($output, 'PRODUCTION_MODULES_OK:') || str_contains($output, 'class="error"')) {
            throw new RuntimeException('Production modules failed: ' . substr(strip_tags($output), 0, 1000));
        }
        $this->output("PASS original VehicleHardpoint imports and DependencyList/DPL execution\n");
    }
}
$maintClass = ProductionSnapshotSmoke::class;
require_once RUN_MAINTENANCE_IF_MAIN;
