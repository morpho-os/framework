<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

use Monolog\Logger;
use Morpho\App\IRouter;
use Morpho\App\ISite;
use Morpho\Ioc\ServiceManager as BaseServiceManager;
use Morpho\Base\NotImplementedException;
use Morpho\Error\ErrorHandler;
use Monolog\Handler\ErrorLogHandler as PhpErrorLogWriter;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;

class ServiceManager extends BaseServiceManager {
    protected function mkInitializerService() {
        return new Initializer($this);
    }

    protected function mkSiteService(): ISite {
        $appConfig = $this['app']->config();
        /** @var ISite $site */
        $site = (new SiteFactory())($appConfig);
        $siteConfig = $site->config();
        $this->setConfig($siteConfig['service']);
        return $site;
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
        $listeners[] = $this->config['errorHandler']['noDupsListener']
            ? new NoDupsListener($logListener)
            : $logListener;
        /*
        if ($this->config['errorHandler']['dumpListener']) {
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
}
