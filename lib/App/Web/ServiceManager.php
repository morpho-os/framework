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
use Morpho\App\ModuleIndex;
use Morpho\App\ModuleIndexer;
use Morpho\Error\DumpListener;
use Morpho\App\ServiceManager as BaseServiceManager;
use Morpho\App\Web\Logging\WebProcessor;
use Morpho\App\Web\Messages\Messenger;
use Morpho\App\Web\Routing\FastRouter;
use Morpho\App\Web\Session\Session;
use Morpho\App\Web\View\PhpTemplateEngine;
use Morpho\App\Web\View\Theme;
use Morpho\Caching\VarExportFileCache;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;
use Morpho\App\ApplyingSiteConfModuleIterator;
use Morpho\App\Web\Routing\RouteMetaProvider;

class ServiceManager extends BaseServiceManager {
    protected function mkRouterService(): IRouter {
        //return new Router($this['db']);
        return new FastRouter();
    }

    protected function mkRouteMetaProviderService() {
        return new RouteMetaProvider();
    }

    protected function mkAppInitializerService() {
        return new AppInitializer($this);
    }

    protected function mkServerModuleIndexService() {
        return new ModuleIndex($this['serverModuleIndexer']);
    }

    protected function mkServerModuleIndexerService() {
        return new ModuleIndexer($this['serverModuleIterator'], new VarExportFileCache($this['site']->conf()['path']['cacheDirPath']), \get_class($this) . '::' . __FUNCTION__);
    }

    protected function mkServerModuleIteratorService() {
        return new ApplyingSiteConfModuleIterator($this);
    }

    protected function mkSessionService() {
        return new Session(__CLASS__);
    }

    protected function mkRequestService() {
        return new Request(null, null);
    }

    protected function mkDebugLoggerService() {
        $logger = new Logger('debug');
        $this->appendLogFileWriter($logger, Logger::DEBUG);
        return $logger;
    }

    protected function mkThemeService() {
        return new Theme($this['templateEngine']);
    }

    protected function mkTemplateEngineService() {
        $templateEngineConf = $this->conf['templateEngine'];
        $templateEngine = new PhpTemplateEngine($this);
        $siteModuleName = $this['site']->moduleName();
        $cacheDirPath = $this['serverModuleIndex']->module($siteModuleName)->cacheDirPath();
        $templateEngine->setCacheDirPath($cacheDirPath);
        $templateEngine->useCache($templateEngineConf['useCache']);
        return $templateEngine;
    }

    protected function mkPluginResolverService(): callable {
        return function (string $pluginName): string {
            $known = [
                'Messenger' => __NAMESPACE__ . '\\View\\MessengerPlugin',
            ];
            return $known[$pluginName];
        };
    }

/*    protected function mkAutoloaderService() {
        return composerAutoloader();
    }*/

    protected function mkMessengerService() {
        return new Messenger();
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

        $conf = $this->conf['errorLogger'];

        if ($conf['errorLogWriter'] && ErrorHandler::isErrorLogEnabled()) {
            $logger->pushHandler(new PhpErrorLogWriter());
        }

        if (!empty($conf['mailWriter']['enabled'])) {
            $logger->pushHandler(
                new NativeMailerLogWriter($conf['mailTo'], 'An error has occurred', $conf['mailFrom'], Logger::NOTICE)
            );
        }

        if ($conf['logFileWriter']) {
            $this->appendLogFileWriter($logger, Logger::DEBUG);
        }

/*        if ($conf['debugWriter']) {
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
        $conf = $this->conf()['dispatchErrorHandler'];
        $dispatchErrorHandler->throwErrors($conf['throwErrors']);
        $dispatchErrorHandler->setExceptionHandler($conf['exceptionHandler']);
        return $dispatchErrorHandler;
    }

    protected function mkErrorHandlerService() {
        $listeners = [];
        $logListener = new LogListener($this['errorLogger']);
        $listeners[] = $this->conf['errorHandler']['noDupsListener']
            ? new NoDupsListener($logListener)
            : $logListener;
        if ($this->conf['errorHandler']['dumpListener']) {
            $listeners[] = new DumpListener();
        }
        return new ErrorHandler($listeners);
    }

    protected function mkActionResultHandlerService() {
        return new ActionResultHandler($this);
    }

    private function appendLogFileWriter(Logger $logger, int $logLevel): void {
        $moduleIndex = $this['serverModuleIndex'];
        $filePath = $moduleIndex->module($this['site']->moduleName())->logDirPath() . '/' . $logger->getName() . '.log';
        $handler = new StreamHandler($filePath, $logLevel);
        $handler->setFormatter(
            new LineFormatter(LineFormatter::SIMPLE_FORMAT . "-------------------------------------------------------------------------------\n", null, true)
        );
        $logger->pushHandler($handler);
    }
}
