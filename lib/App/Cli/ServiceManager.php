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
use Morpho\Base\NotImplementedException;
use Morpho\Error\ErrorHandler;
use Monolog\Handler\ErrorLogHandler as PhpErrorLogWriter;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;
use Morpho\Base\EventManager;

class ServiceManager extends BaseServiceManager {
    protected function mkAppInitializerService() {
        return new AppInitializer($this);
    }

    protected function mkErrorLoggerService() {
        $logger = new Logger('error');

        if (ErrorHandler::isErrorLogEnabled()) {
            $logger->pushHandler(new PhpErrorLogWriter());
        }

        $logger->pushHandler(new class extends \Monolog\Handler\AbstractProcessingHandler {
            protected function write(array $record): void {
                errorLn($record['message']);
            }
        });

        return $logger;
    }

    protected function mkErrorHandlerService() {
        $listeners = [];
        $logListener = new LogListener($this['errorLogger']);
        $listeners[] = $this->conf['errorHandler']['noDupsListener']
            ? new NoDupsListener($logListener)
            : $logListener;
        /*
        if ($this->conf['errorHandler']['dumpListener']) {
            $listeners[] = new DumpListener();
        }
        */
        return new ErrorHandler($listeners);
    }

    protected function mkRequestService() {
        return new Request();
    }

    protected function mkRouterService(): IRouter {
        throw new NotImplementedException();
    }

    protected function mkEventManagerService() {
        $eventManager = new EventManager();
        $eventManager->on('dispatchError', function ($event) {
            throw $event->args['exception'];
        });
        return $eventManager;
    }
}
