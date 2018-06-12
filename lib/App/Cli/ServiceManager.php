<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

use Monolog\Logger;
use Morpho\App\IRouter;
use Morpho\App\ServiceManager as BaseServiceManager;

class ServiceManager extends BaseServiceManager {
    protected function mkErrorLoggerService() {
        $logger = new Logger('error');
        return $logger;
    }

    protected function mkRequestService() {
        return new Request();
    }

    /*abstract protected function mkDispatcherService();*/
    protected function mkRouterService(): IRouter {
        // TODO: Implement newRouterService() method.
    }
}
