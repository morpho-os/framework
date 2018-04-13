<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Morpho\Caching\VarExportFileCache;
use Morpho\App\Core\IRouter;
use Morpho\App\Core\ModuleIndex;
use Morpho\App\Core\ModuleIndexer;
use Morpho\App\Core\ServiceManager as BaseServiceManager;
use Morpho\Error\ErrorHandler;
use Morpho\App\Web\Logging\WebProcessor;
use Morpho\App\Web\Messages\Messenger;
use Morpho\App\Web\Routing\FastRouter;
use Morpho\App\Web\Session\Session;
use Morpho\App\Web\Uri\UriChecker;
use Morpho\App\Web\View\PhpTemplateEngine;
use Morpho\App\Web\View\Theme;

class ServiceManager extends BaseServiceManager {
    public function newRouterService(): IRouter {
        //return new Router($this['db']);
        return new FastRouter();
    }

/*    protected function newDbService() {
        return Db::connect($this->config['db']);
    }*/

    protected function newModuleIndexerService() {
        return new ModuleIndexer(new VarExportFileCache($this['site']->config()['paths']['cacheDirPath']));
    }

    protected function newModuleMetaIteratorService() {
        return new ModuleMetaIterator($this);
    }

    protected function newSessionService() {
        return new Session(__CLASS__);
    }

    protected function newRequestService() {
        return new Request(null, null, new UriChecker($this));
    }

    protected function newDebugLoggerService() {
        $logger = new Logger('debug');
        $this->appendSiteLogFileWriter($logger, Logger::DEBUG);
        return $logger;
    }

    protected function newModuleIndexService() {
        return new ModuleIndex($this['moduleIndexer']);
    }

    protected function newThemeService() {
        return new Theme($this['templateEngine']);
    }

    protected function newTemplateEngineService() {
        $templateEngineConfig = $this->config['templateEngine'];
        $templateEngine = new PhpTemplateEngine($this);
        $siteModuleName = $this['site']->moduleName();
        $cacheDirPath = $this['moduleIndex']->moduleMeta($siteModuleName)->cacheDirPath();
        $templateEngine->setCacheDirPath($cacheDirPath);
        $templateEngine->useCache($templateEngineConfig['useCache']);
        return $templateEngine;
    }

/*    protected function newAutoloaderService() {
        return composerAutoloader();
    }*/

    protected function newMessengerService() {
        return new Messenger();
    }

    protected function newInstanceProviderService() {
        return new InstanceProvider($this);
    }

    protected function newDispatcherService() {
        return new Dispatcher(
            $this['instanceProvider'],
            $this['eventManager']
        );
    }

    protected function newEventManagerService() {
        return new EventManager($this);
    }

    protected function newErrorLoggerService() {
        $logger = (new Logger('error'))
            ->pushProcessor(new WebProcessor())
            ->pushProcessor(new MemoryUsageProcessor())
            ->pushProcessor(new MemoryPeakUsageProcessor())
            ->pushProcessor(new IntrospectionProcessor());

        if (ErrorHandler::isErrorLogEnabled()) {
            $logger->pushHandler(new ErrorLogHandler());
        }

        $config = $this->config['errorLogger'];
        if ($config['mailOnError']) {
            $logger->pushHandler(
                new NativeMailerHandler($config['mailTo'], 'An error has occurred', $config['mailFrom'], Logger::NOTICE)
            );
        }

        if ($config['logToFile']) {
            $this->appendSiteLogFileWriter($logger, Logger::DEBUG);
        }

        return $logger;
    }

    protected function newContentNegotiatorService() {
        return new ContentNegotiator();
    }

    protected function newDispatchErrorHandlerService() {
        return new DispatchErrorHandler();
    }

    private function appendSiteLogFileWriter($logger, int $logLevel) {
        $moduleIndex = $this['moduleIndex'];
        $filePath = $moduleIndex->moduleMeta($this['site']->moduleName())->logDirPath() . '/' . $logger->getName() . '.log';
        $handler = new StreamHandler($filePath, $logLevel);
        $handler->setFormatter(
            new LineFormatter(LineFormatter::SIMPLE_FORMAT . "-------------------------------------------------------------------------------\n", null, true)
        );
        $logger->pushHandler($handler);
    }
}
