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
use Morpho\Di\ServiceManager as BaseServiceManager;
use Morpho\Error\DumpListener;
use Morpho\Error\ErrorHandler;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;
use Morpho\Web\Logging\WebProcessor;
use Morpho\Web\Messages\Messenger;
use Morpho\Web\Routing\FastRouter;
use Morpho\Web\Session\Session;
use Morpho\Web\View\Compiler;
use Morpho\Web\View\FormPersister;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Web\View\PostHtmlParser;
use Morpho\Web\View\PreHtmlParser;
use function Morpho\Code\composerAutoloader;

class ServiceManager extends BaseServiceManager {
    protected $config = [];

    public function __construct(array $services = null, array $config = null) {
        parent::__construct($services);
        $this->config = (array) $config;
    }

    public function setConfig(array $config): void {
        $this->config = $config;
    }

    public function config(): array {
        return $this->config;
    }

    protected function newErrorHandlerService() {
        $listeners = [];
        $logListener = new LogListener($this->get('errorLogger'));
        $listeners[] = $this->config['errorHandler']['noDupsListener']
            ? new NoDupsListener($logListener)
            : $logListener;
        if ($this->config['errorHandler']['dumpListener']) {
            $listeners[] = new DumpListener();
        }
        return new ErrorHandler($listeners);
    }

    public function newRouterService() {
        //return new Router($this->get('db'));
        return new FastRouter();
    }

/*    protected function newDbService() {
        return Db::connect($this->config['db']);
    }*/

    protected function newSessionService() {
        return new Session(__CLASS__);
    }

    protected function newRequestService() {
        return new Request();
    }

    protected function newEnvironmentService() {
        return new Environment();
    }
    
    protected function newDebugLoggerService() {
        $logger = new Logger('debug');
        $this->appendSiteLogFileWriter($logger, Logger::DEBUG);
        return $logger;
    }

    protected function newTemplateEngineService() {
        $templateEngineConfig = $this->config['templateEngine'];
        $templateEngine = new PhpTemplateEngine();
        $templateEngine->setCacheDirPath($this->get('site')->pathManager()->cacheDirPath());
        $templateEngine->useCache($templateEngineConfig['useCache']);
        $templateEngine->append(new PreHtmlParser($this))
            ->append(new FormPersister($this))
            ->append(new Compiler())
            ->append(new PostHtmlParser($this/*, $templateEngineConfig['forceCompileTs'], $templateEngineConfig['nodeBinDirPath'], $templateEngineConfig['tsOptions']*/));
        return $templateEngine;
    }

    protected function newAutoloaderService() {
        return composerAutoloader();
    }

    protected function newConfigManagerService() {
        return new ConfigManager();
    }

    protected function newMessengerService() {
        return new Messenger();
    }

    protected function newModuleProviderService() {
        return new ModuleProvider($this->get('pathManager'));
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

    private function appendSiteLogFileWriter($logger, int $logLevel) {
        $site = $this->get('site');
        $filePath = $site->pathManager()->logDirPath() . '/' . $logger->getName() . '.log';
        $handler = new StreamHandler($filePath, $logLevel);
        $handler->setFormatter(
            new LineFormatter(LineFormatter::SIMPLE_FORMAT . "-------------------------------------------------------------------------------\n", null, true)
        );
        $logger->pushHandler($handler);
    }
}
