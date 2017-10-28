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
use Morpho\Core\IRouter;
use const Morpho\Core\MODULE_DIR_NAME;
use Morpho\Core\ModuleProvider;
use Morpho\Core\ServiceManager as BaseServiceManager;
use Morpho\Error\ErrorHandler;
use Morpho\Fs\Directory;
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
    public function newRouterService(): IRouter {
        //return new Router($this->get('db'));
        return new FastRouter();
    }

/*    protected function newDbService() {
        return Db::connect($this->config['db']);
    }*/

    protected function newModuleIndexerService() {
        $site = $this->get('site');
        $siteConfig = $site->config();
        return new ModuleIndexer(
            $this->get('moduleDirsIterator'),
            $siteConfig['paths']['cacheDirPath'] . '/module-index.php',
            [
                $site->moduleName() => $siteConfig,
            ],
            // @TODO: Add module iterator
            array_keys($siteConfig['modules'])
        );
    }

    protected function newModuleDirsIteratorService() {
        $baseModuleDirPath = $this->get('app')->config()['baseDirPath'] . '/' . MODULE_DIR_NAME;
        return Directory::dirPaths($baseModuleDirPath, null, ['recursive' => false]);
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

    protected function newModuleIndexService() {
        return new ModuleIndex($this->get('moduleIndexer'));
    }

    protected function newTemplateEngineService() {
        $templateEngineConfig = $this->config['templateEngine'];
        $templateEngine = new PhpTemplateEngine();
        $cacheDirPath = $this->get('moduleIndex')->moduleMeta($this->get('site')->moduleName())->cacheDirPath();
        $templateEngine->setCacheDirPath($cacheDirPath);
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