<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Morpho\Caching\VarExportFileCache;
use Morpho\Core\IRouter;
use Morpho\Core\ModuleIndex;
use Morpho\Core\ModuleIndexer;
use Morpho\Core\ModuleProvider;
use Morpho\Core\ServiceManager as BaseServiceManager;
use Morpho\Error\ErrorHandler;
use Morpho\Web\Logging\WebProcessor;
use Morpho\Web\Messages\Messenger;
use Morpho\Web\Routing\FastRouter;
use Morpho\Web\Session\Session;
use Morpho\Web\Uri\UriChecker;
use Morpho\Web\View\Compiler;
use Morpho\Web\View\FormPersister;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Web\View\RendererFactory;
use Morpho\Web\View\ScriptProcessor;
use Morpho\Web\View\UriProcessor;
use function Morpho\Code\composerAutoloader;
use Morpho\Web\View\Theme;

class ServiceManager extends BaseServiceManager {
    public function newRouterService(): IRouter {
        //return new Router($this->get('db'));
        return new FastRouter();
    }

/*    protected function newDbService() {
        return Db::connect($this->config['db']);
    }*/

    protected function newModuleIndexerService() {
        return new ModuleIndexer(new VarExportFileCache($this->get('site')->config()['paths']['cacheDirPath']));
    }

    protected function newModuleMetaProviderService() {
        return new ModuleMetaProvider($this);
    }

    protected function newSessionService() {
        return new Session(__CLASS__);
    }

    protected function newRequestService() {
        return new Request(null, new UriChecker($this));
    }

    protected function newEnvironmentService() {
        return new Environment();
    }
    
    protected function newDebugLoggerService() {
        $logger = new Logger('debug');
        $this->appendSiteLogFileWriter($logger, Logger::DEBUG);
        return $logger;
    }

    protected function newModuleIndexService() {
        return new ModuleIndex($this->get('moduleIndexer'));
    }

    protected function newThemeService() {
        return new Theme($this->get('templateEngine'));
    }

    protected function newTemplateEngineService() {
        $templateEngineConfig = $this->config['templateEngine'];
        $templateEngine = new PhpTemplateEngine();
        $cacheDirPath = $this->get('moduleIndex')->moduleMeta($this->get('site')->moduleName())->cacheDirPath();
        $templateEngine->setCacheDirPath($cacheDirPath);
        $templateEngine->useCache($templateEngineConfig['useCache']);
        $templateEngine->append(new Compiler())
            ->append(new FormPersister($this))
            ->append(new UriProcessor($this))
            ->append(new ScriptProcessor($this/*, $templateEngineConfig['forceCompileTs'], $templateEngineConfig['nodeBinDirPath'], $templateEngineConfig['tsOptions']*/));
        return $templateEngine;
    }

    protected function newAutoloaderService() {
        return composerAutoloader();
    }

    protected function newMessengerService() {
        return new Messenger();
    }

    protected function newModuleProviderService() {
        return new ModuleProvider($this->get('moduleIndex'));
    }

    protected function newDispatcherService() {
        return new Dispatcher(
            $this->get('moduleProvider'),
            $this->get('eventManager')
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
        $moduleIndex = $this->get('moduleIndex');
        $filePath = $moduleIndex->moduleMeta($this->get('site')->moduleName())->logDirPath() . '/' . $logger->getName() . '.log';
        $handler = new StreamHandler($filePath, $logLevel);
        $handler->setFormatter(
            new LineFormatter(LineFormatter::SIMPLE_FORMAT . "-------------------------------------------------------------------------------\n", null, true)
        );
        $logger->pushHandler($handler);
    }
}