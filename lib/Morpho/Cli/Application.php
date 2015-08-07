<?php
namespace Morpho\Cli;

use Morpho\Core\Application as BaseApplication;

class Application extends BaseApplication {
    protected function createServiceManager() {
        $serviceManager = new ServiceManager([]);

        $serviceManager->set('app', $this);

        return $serviceManager;
    }

    protected function handleErrorOrException(\Throwable $e) {
        echo (string)$e;
    }
}
