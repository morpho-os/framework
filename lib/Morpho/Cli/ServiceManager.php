<?php
namespace Morpho\Cli;

use Morpho\Core\ServiceManager as BaseServiceManager;

class ServiceManager extends BaseServiceManager {
    protected function createRequestService() {
        return new Request();
    }

    protected function createRouterService() {
        return new Router();
    }

    protected function createModuleManagerService() {
        $this->get('moduleAutoloader')->register();
        $moduleManager = new ModuleManager($this->get('db'));
        $moduleManager->isFallbackMode(true);
        return $moduleManager;
    }

    protected function getAppConfig() {
        return [
            'moduleAutoloader' => [
                'useCache' => false,
            ],
            'cacheDirPath' => SITE_DIR_PATH . '/default/cache',
            'db' => [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ],
            'view' => 'console',
        ];
    }
}
