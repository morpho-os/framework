<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use const Morpho\Core\MODULE_DIR_PATH;
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
use Morpho\Web\Routing\FastRouter;
use Morpho\Web\Routing\RoutesMetaProvider;
use Morpho\Web\Session\Session;
use Morpho\Web\View\Compiler;
use Morpho\Web\View\FormPersister;
use Morpho\Web\View\PostHtmlParser;
use Morpho\Web\View\PreHtmlParser;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Error\DumpListener;
use Morpho\Error\ErrorHandler;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;
use Morpho\Db\Sql\Db;

class ServiceManager extends BaseServiceManager {
    protected $config;

    public function __construct(array $services = null) {
        parent::__construct($services);
        $this->config = $services['site']->config();
    }

    public function newRouterService() {
        //return new Router($this->get('db'));
        return new FastRouter();
    }

    protected function newDbService() {
        return Db::connect($this->config['db']);
    }

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
        $templateEngine->setCacheDirPath($this->get('site')->cacheDirPath());
        $templateEngine->useCache($templateEngineConfig['useCache']);
        $templateEngine->append(new PreHtmlParser($this))
            ->append(new FormPersister($this))
            ->append(new Compiler())
            ->append(new PostHtmlParser($this/*, $templateEngineConfig['forceCompileTs'], $templateEngineConfig['nodeBinDirPath'], $templateEngineConfig['tsOptions']*/));
        return $templateEngine;
    }

    protected function newMessengerService() {
        return new Messenger();
    }
    
    protected function newModuleFsService() {
        return new ModuleFs(MODULE_DIR_PATH);//, $this->get('autoloader'));
    }

    protected function newModuleManagerService() {
        $db = $this->get('db');
        $moduleFs = $this->get('moduleFs');
        $moduleManager = new ModuleManager($db, $moduleFs);
        // Replace the site, so that only one site would be available.
        $moduleManager->setServiceManager($this);
        $site = $this->get('site');
        $site1 = $moduleManager->offsetGet($site->name());
        $site1->setSite($site);
        $this->set('site', $site1);
        return $moduleManager;
    }

    protected function newErrorHandlerService() {
        $listeners = [];
        $listeners[] = new NoDupsListener(new LogListener($this->get('errorLogger')));
        if ($this->config['errorHandler']['addDumpListener']) {
            $listeners[] = new DumpListener();
        }
        return new ErrorHandler($listeners);
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

    protected function newRoutesMetaProviderService() {
        $routesMetaProvider = new RoutesMetaProvider();
        $actionsMetaProvider = new ActionsMetaProvider($this->get('moduleManager'));
        $routesMetaProvider->setActionsMetaProvider($actionsMetaProvider);
        return $routesMetaProvider;
    }

    private function appendSiteLogFileWriter($logger, int $logLevel) {
        $site = $this->get('site');
        $filePath = $site->logDirPath() . '/' . $logger->getName() . '.log';
        $handler = new StreamHandler($filePath, $logLevel);
        $handler->setFormatter(
            new LineFormatter(LineFormatter::SIMPLE_FORMAT . "-------------------------------------------------------------------------------\n", null, true)
        );
        $logger->pushHandler($handler);
    }
}
