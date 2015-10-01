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
use Morpho\Logger\WebProcessor;
use Morpho\Web\View\Compiler;
use Morpho\Web\View\HtmlParser;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Error\CompositeListener;
use Morpho\Error\DumpListener;
use Morpho\Error\ErrorHandler;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;

class ServiceManager extends BaseServiceManager {
    public function createRouterService() {
        if ($this->isFallbackMode()) {
            return new FallbackRouter();
        }
        //return new Router($this->get('db'));
        return new FastRouter();
    }

    protected function createSessionService() {
        return new Session(__CLASS__);
    }

    protected function createRequestService() {
        return new Request();
    }

    protected function createTemplateEngineService() {
        $templateEngineConfig = $this->config['templateEngine'];
        $templateEngine = new PhpTemplateEngine();
        $templateEngine->setCacheDirPath($this->get('siteManager')->getCurrentSite()->getCacheDirPath());
        $templateEngine->useCache($templateEngineConfig['useCache']);
        $templateEngine->attach(new Compiler())
            ->attach(new HtmlParser($this));
        return $templateEngine;
    }

    protected function createMessengerService() {
        return new Messenger();
    }

    protected function createAccessManagerService() {
        return new AccessManager($this->get('session'), $this->get('db'));
    }

    protected function createModuleManagerService() {
        $this->get('moduleAutoloader')->register();
        $moduleManager = new ModuleManager($this->get('db'));
        $moduleManager->isFallbackMode($this->isFallbackMode());
        return $moduleManager;
    }

    protected function createErrorHandlerService() {
        $logger = $this->get('logger');

        $siteManager = $this->get('siteManager');
        $site = $siteManager->getCurrentSite();

        if (!empty(ini_get('error_log'))) {
            $logger->pushHandler(new ErrorLogHandler());
        }

        if ($site->isProductionMode()) {
            // @TODO: Change subject (a new MailHandler may be required for that).
            $logger->pushHandler(
                new NativeMailerHandler($this->config['mailTo'], 'An error has occurred', Logger::NOTICE)
            );
        }

        $handler = $this->createStreamHandlerForLogger(
            $site->getLogDirPath() . '/' . $site->getMode() . '-error.log'
        );
        $logger->pushHandler($handler);

        $listener = new LogListener($logger);
        if ($this->get('siteManager')->getCurrentSite()->isDevMode()) {
            $listener = new CompositeListener(
                [
                    $listener,
                    new DumpListener()
                ]
            );
        }
        return new ErrorHandler([new NoDupsListener($listener)]);
    }

    protected function createLoggerService() {
        $logger = new Logger('default');

        $site = $this->get('siteManager')->getCurrentSite();

        if ($site->isDebug()) {
            $logger->pushHandler(
                $this->createStreamHandlerForLogger($site->getLogDirPath() . '/' . $site->getMode() . '-debug.log')
            );
        }

        $logger->pushProcessor(new WebProcessor())
            ->pushProcessor(new MemoryUsageProcessor())
            ->pushProcessor(new MemoryPeakUsageProcessor())
            ->pushProcessor(new IntrospectionProcessor());

        return $logger;
    }

    protected function isFallbackMode() {
        return $this->get('siteManager')->isFallbackMode();
    }

    protected function createStreamHandlerForLogger($filePath) {
        $handler = new StreamHandler($filePath);
        $handler->setFormatter(
            new LineFormatter(LineFormatter::SIMPLE_FORMAT . "-------------------------------------------------------------------------------\n")
        );
        return $handler;
    }
}
