<?php
// Private local evidence only. Run against the restored, isolated database.
require_once '/var/www/mediawiki/maintenance/Maintenance.php';
class ExportProductionModules extends Maintenance {
    public function execute() {
        $services = MediaWiki\MediaWikiServices::getInstance();
        $db = $services->getDBLoadBalancer()->getConnection(DB_REPLICA);
        $pages = $db->select('page', ['page_id', 'page_title'], ['page_namespace'=>828], __METHOD__);
        $modules = [];
        foreach ($pages as $row) {
            $page = $services->getWikiPageFactory()->newFromID((int)$row->page_id);
            $content = $page->getContent();
            if ($content instanceof TextContent) {
                $modules[$row->page_title] = $content->getText();
            }
        }
        file_put_contents('/tmp/production-modules.json', json_encode($modules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $this->output('Exported ' . count($modules) . " module pages into private local evidence\n");
    }
}
$maintClass = ExportProductionModules::class;
require_once RUN_MAINTENANCE_IF_MAIN;
