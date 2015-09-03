<?php
declare(strict_types=1);

namespace Morpho\Cli;

use Morpho\Core\Application as BaseApplication;
use Morpho\Di\IServiceManager;

class Application extends BaseApplication {
    protected function createServiceManager(): IServiceManager {
        $serviceManager = new ServiceManager([]);

        $serviceManager->set('app', $this);

        return $serviceManager;
    }

    protected function handleException(\Exception $e, IServiceManager $serviceManager = null) {
        echo (string)$e;
    }

    protected function returnResult($result) {
        return $result === true
            ? Environment::SUCCESS_EXIT_CODE
            : Environment::ERROR_EXIT_CODE;
    }
}
