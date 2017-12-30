<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Cli;

use Monolog\Logger;
use Morpho\Core\ServiceManager as BaseServiceManager;

abstract class ServiceManager extends BaseServiceManager {
    protected function newEnvironmentService() {
        return new Environment();
    }

    protected function newErrorLoggerService() {
        $logger = new Logger('error');
        return $logger;
    }

    protected function newRequestService() {
        return new Request();
    }

/*    protected function newResponseService() {
        return new Response();
    }
*/

    /*abstract protected function newDispatcherService();*/
}