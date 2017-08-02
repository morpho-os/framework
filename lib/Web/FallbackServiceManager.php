<?php
//declare(strict_types=1);
namespace Morpho\Web;

use Morpho\Web\Routing\FallbackRouter;
use Morpho\Db\Sql\Db;

class FallbackServiceManager extends ServiceManager {
    public function newRouterService() {
        return new FallbackRouter();
    }

    protected function newDbService() {
        $dbConfig = $this->config['db'];
        // Don't connect for the fallback mode.
        $dbConfig['db'] = '';
        return Db::connect($dbConfig);
    }

    protected function newModuleManagerService() {
        $db = $this->get('db');
        $moduleFs = $this->get('moduleFs');
        $moduleManager = new FallbackModuleManager($db, $moduleFs);
        // Replace the site, so that only one site would be available.
        $moduleManager->setServiceManager($this);
        $site = $this->get('site');
        $site1 = $moduleManager->offsetGet($site->name());
        $site1->setSite($site);
        $this->set('site', $site1);
        return $moduleManager;
    }
}