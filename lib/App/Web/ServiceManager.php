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
use Morpho\App\InstanceProvider;
use Morpho\App\Web\View\ActionResultRenderer;
use Morpho\Caching\VarExportFileCache;
use Morpho\App\IRouter;
use Morpho\App\ModuleIndex;
use Morpho\App\ModuleIndexer;
use Morpho\App\ServiceManager as BaseServiceManager;
use Morpho\Error\ErrorHandler;
use Morpho\App\Web\Logging\WebProcessor;
use Morpho\App\Web\Messages\Messenger;
use Morpho\App\Web\Routing\FastRouter;
use Morpho\App\Web\Session\Session;
use Morpho\App\Web\Uri\UriChecker;
use Morpho\App\Web\View\PhpTemplateEngine;
use Morpho\App\Web\View\Theme;

class ServiceManager extends BaseServiceManager {
    public function mkRouterService(): IRouter {
        //return new Router($this['db']);
        return new FastRouter();
    }

/*    protected function mkDbService() {
        return Db::connect($this->config['db']);
    }*/

    protected function mkModuleIndexerService() {
        return new ModuleIndexer(new VarExportFileCache($this['site']->config()['path']['cacheDirPath']));
    }

    protected function mkModuleMetaIteratorService() {
        return new ModuleMetaIterator($this);
    }

    protected function mkSessionService() {
        return new Session(__CLASS__);
    }

    protected function mkRequestService() {
        return new Request(null, null, new UriChecker($this));
    }

    protected function mkDebugLoggerService() {
        $logger = new Logger('debug');
        $this->appendSiteLogFileWriter($logger, Logger::DEBUG);
        return $logger;
    }

    protected function mkModuleIndexService() {
        return new ModuleIndex($this['moduleIndexer']);
    }

    protected function mkThemeService() {
        return new Theme($this['templateEngine']);
    }

    protected function mkTemplateEngineService() {
        $templateEngineConfig = $this->config['templateEngine'];
        $templateEngine = new PhpTemplateEngine($this);
        $siteModuleName = $this['site']->moduleName();
        $cacheDirPath = $this['moduleIndex']->moduleMeta($siteModuleName)->cacheDirPath();
        $templateEngine->setCacheDirPath($cacheDirPath);
        $templateEngine->useCache($templateEngineConfig['useCache']);
        return $templateEngine;
    }

/*    protected function mkAutoloaderService() {
        return composerAutoloader();
    }*/

    protected function mkMessengerService() {
        return new Messenger();
    }

    protected function mkInstanceProviderService() {
        return new InstanceProvider($this);
    }

    protected function mkDispatcherService() {
        return new Dispatcher(
            $this['instanceProvider'],
            $this['eventManager']
        );
    }

    protected function mkEventManagerService() {
        return new EventManager($this);
    }

    protected function mkErrorLoggerService() {
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

    protected function mkContentNegotiatorService() {
        return new ContentNegotiator();
    }

    protected function mkDispatchErrorHandlerService() {
        return new DispatchErrorHandler();
    }

    protected function mkActionResultRendererService() {
        return new ActionResultRenderer($this);
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
