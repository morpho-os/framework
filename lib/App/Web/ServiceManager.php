<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler as PhpErrorLogWriter;
use Monolog\Handler\NativeMailerHandler as NativeMailerLogWriter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Morpho\App\Dispatcher;
use Morpho\App\IRouter;
use Morpho\App\ISite;
use Morpho\App\ModuleIndex;
use Morpho\App\ModuleIndexer;
use Morpho\Error\DumpListener;
use Morpho\Ioc\ServiceManager as BaseServiceManager;
use Morpho\App\Web\Logging\WebProcessor;
use Morpho\App\Web\Messages\Messenger;
use Morpho\App\Web\Routing\FastRouter;
use Morpho\App\Web\Session\Session;
use Morpho\App\Web\Uri\UriChecker;
use Morpho\App\Web\View\PhpTemplateEngine;
use Morpho\App\Web\View\Theme;
use Morpho\Caching\VarExportFileCache;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;

class ServiceManager extends BaseServiceManager {
    public function mkRouterService(): IRouter {
        //return new Router($this['db']);
        return new FastRouter();
    }

/*    protected function mkDbService() {
        return Db::connect($this->config['db']);
    }*/

    public function mkSiteService(): ISite {
        $appConfig = $this['app']->config();
        /** @var ISite $site */
        $site = (new SiteFactory())($appConfig);
        $siteConfig = $site->config();
        $this->setConfig($siteConfig['service']);
        return $site;
    }

    protected function mkInitializerService() {
        return new Initializer($this);
    }

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
        $this->appendLogFileWriter($logger, Logger::DEBUG);
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

        $config = $this->config['errorLogger'];

        if ($config['errorLogWriter'] && ErrorHandler::isErrorLogEnabled()) {
            $logger->pushHandler(new PhpErrorLogWriter());
        }

        if (!empty($config['mailWriter']['enabled'])) {
            $logger->pushHandler(
                new NativeMailerLogWriter($config['mailTo'], 'An error has occurred', $config['mailFrom'], Logger::NOTICE)
            );
        }

        if ($config['logFileWriter']) {
            $this->appendLogFileWriter($logger, Logger::DEBUG);
        }

/*        if ($config['debugWriter']) {
            $logger->pushHandler(new class extends \Monolog\Handler\AbstractProcessingHandler {
                protected function write(array $record) {
                    d($record['message']);
                }
            });
        }*/

        return $logger;
    }

    protected function mkContentNegotiatorService() {
        return new ContentNegotiator();
    }

    protected function mkDispatchErrorHandlerService() {
        $dispatchErrorHandler = new DispatchErrorHandler();
        $config = $this->config()['dispatchErrorHandler'];
        $dispatchErrorHandler->throwErrors($config['throwErrors']);
        $dispatchErrorHandler->setExceptionHandler($config['exceptionHandler']);
        return $dispatchErrorHandler;
    }

    protected function mkErrorHandlerService() {
        $listeners = [];
        $logListener = new LogListener($this['errorLogger']);
        $listeners[] = $this->config['errorHandler']['noDupsListener']
            ? new NoDupsListener($logListener)
            : $logListener;
        if ($this->config['errorHandler']['dumpListener']) {
            $listeners[] = new DumpListener();
        }
        return new ErrorHandler($listeners);
    }

    protected function mkActionResultHandlerService() {
        return new ActionResultHandler($this);
    }

    private function appendLogFileWriter(Logger $logger, int $logLevel): void {
        $moduleIndex = $this['moduleIndex'];
        $filePath = $moduleIndex->moduleMeta($this['site']->moduleName())->logDirPath() . '/' . $logger->getName() . '.log';
        $handler = new StreamHandler($filePath, $logLevel);
        $handler->setFormatter(
            new LineFormatter(LineFormatter::SIMPLE_FORMAT . "-------------------------------------------------------------------------------\n", null, true)
        );
        $logger->pushHandler($handler);
    }
}
