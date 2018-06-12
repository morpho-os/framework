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
    protected function newErrorLoggerService() {
        $logger = new Logger('error');
        return $logger;
    }

    protected function newRequestService() {
        return new Request();
    }

    /*abstract protected function newDispatcherService();*/
    protected function newRouterService(): IRouter {
        // TODO: Implement newRouterService() method.
    }
}
