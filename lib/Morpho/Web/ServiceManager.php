<?php
namespace Morpho\Web;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Morpho\Core\ServiceManager as BaseServiceManager;
use Morpho\Web\Logging\WebProcessor;
use Morpho\Web\Messages\Messenger;
use Morpho\Web\Routing\ActionsMetaProvider;
use Morpho\Web\Routing\FallbackRouter;
use Morpho\Web\Routing\FastRouter;
use Morpho\Web\Routing\RoutesMetaProvider;
use Morpho\Web\View\Compiler;
use Morpho\Web\View\FormPersister;
use Morpho\Web\View\HtmlParserPost;
use Morpho\Web\View\HtmlParserPre;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Error\DumpListener;
use Morpho\Error\ErrorHandler;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;
use Morpho\Db\Sql\Db;

class ServiceManager extends BaseServiceManager {
    public function createRouterService() {
        if ($this->isFallbackMode()) {
            return new FallbackRouter();
        }
        //return new Router($this->get('db'));
        return new FastRouter();
    }

    protected function createDbService() {
        $dbConfig = $this->config['db'];
        if ($this->isFallbackMode()) {
            // Don't connect for the fallback mode.
            $dbConfig['db'] = '';
        }
        return new Db($dbConfig);
    }

    protected function createSessionService() {
        return new Session(__CLASS__);
    }

    protected function createRequestService() {
        return new Request();
    }

    protected function createEnvironmentService() {
        return new Environment();
    }
    
    protected function createDebugLoggerService() {
        $logger = new Logger('debug');
        $this->appendSiteLogFileWriter($logger, Logger::DEBUG);
        return $logger;
    }

    protected function createTemplateEngineService() {
        $templateEngineConfig = $this->config['templateEngine'];
        $templateEngine = new PhpTemplateEngine();
        $templateEngine->setCacheDirPath($this->get('siteManager')->getCurrentSite()->getCacheDirPath());
        $templateEngine->useCache($templateEngineConfig['useCache']);
        $templateEngine->attach(new HtmlParserPre($this))
            ->attach(new FormPersister($this))
            ->attach(new Compiler())
            ->attach(new HtmlParserPost($this, $templateEngineConfig['forceCompileTs'], $templateEngineConfig['nodeBinDirPath'], $templateEngineConfig['tsOptions']));
        return $templateEngine;
    }

    protected function createMessengerService() {
        return new Messenger();
    }
    
    protected function createModuleFsService() {
        return new ModuleFs(MODULE_DIR_PATH, $this->get('autoloader'));
    }

    protected function createModuleManagerService() {
        $moduleManager = new ModuleManager(
            $this->get('db'),
            $this->get('moduleFs')
        );
        $moduleManager->isFallbackMode($this->isFallbackMode());
        return $moduleManager;
    }

    protected function createErrorHandlerService() {
        $listeners = [];
        $listeners[] = new NoDupsListener(new LogListener($this->get('errorLogger')));
        if ($this->config['errorHandler']['addDumpListener']) {
            $listeners[] = new DumpListener();
        }
        return new ErrorHandler($listeners);
    }

    protected function createErrorLoggerService() {
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

    protected function createRoutesMetaProviderService() {
        $routesMetaProvider = new RoutesMetaProvider();
        $actionsMetaProvider = new ActionsMetaProvider();
        $actionsMetaProvider->setServiceManager($this);
        $actionsMetaProvider->setModuleManager($this->get('moduleManager'));
        $routesMetaProvider->setActionsMetaProvider($actionsMetaProvider);
        return $routesMetaProvider;
    }

    protected function isFallbackMode() {
        return $this->get('siteManager')->isFallbackMode();
    }

    private function appendSiteLogFileWriter($logger, int $logLevel) {
        $site = $this->get('siteManager')->getCurrentSite();
        $filePath = $site->getLogDirPath() . '/' . $logger->getName() . '.log';
        $handler = new StreamHandler($filePath, $logLevel);
        $handler->setFormatter(
            new LineFormatter(LineFormatter::SIMPLE_FORMAT . "-------------------------------------------------------------------------------\n", null, true)
        );
        $logger->pushHandler($handler);
    }
}
