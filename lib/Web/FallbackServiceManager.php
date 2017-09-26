<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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
        $fs = $this->get('fs');
        $moduleManager = new FallbackModuleManager($db, $fs);
        return $moduleManager;
    }
}