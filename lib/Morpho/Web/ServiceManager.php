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
use Morpho\Identity\UserManager;
use Morpho\Logger\WebProcessor;
use Morpho\Web\View\Compiler;
use Morpho\Web\View\HtmlParserPost;
use Morpho\Web\View\HtmlParserPre;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Error\DumpListener;
use Morpho\Error\ErrorHandler;
use Morpho\Error\LogListener;
use Morpho\Error\NoDupsListener;
use Morpho\Base\Environment;

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
        $templateEngine->attach(new HtmlParserPre($this))
            ->attach(new Compiler())
            ->attach(new HtmlParserPost($this));
        return $templateEngine;
    }

    protected function createMessengerService() {
        return new Messenger();
    }

    protected function createModuleManagerService() {
        $this->get('moduleAutoloader')->register();
        $moduleManager = new ModuleManager($this->get('db'));
        $moduleManager->isFallbackMode($this->isFallbackMode());
        return $moduleManager;
    }

    protected function createErrorHandlerService() {
        $logger = $this->createLogger('error');

        if (Environment::isIniSet('log_errors') && !empty(ini_get('error_log'))) {
            $logger->pushHandler(new ErrorLogHandler());
        }

        $siteManager = $this->get('siteManager');
        $site = $siteManager->getCurrentSite();

        if ($site->isProductionMode()) {
            // @TODO: Change subject (a new MailHandler may be required for that).
            $logger->pushHandler(
                new NativeMailerHandler($this->config['mailTo'], 'An error has occurred', Logger::NOTICE)
            );
        }

        $logger->pushHandler(
            $this->createStreamHandlerForLogger(
                $site->getLogDirPath() . '/' . $site->getMode() . '-error.log',
                Logger::NOTICE
            )
        );

        $listeners = [];

        $listeners[] = $site->isDebug()
            ? new LogListener($logger)
            : new NoDupsListener(new LogListener($logger));

        if ($site->isDevMode() || $site->isDebug()) {
            $listeners[] = new DumpListener();
        }

        return new ErrorHandler($listeners);
    }

    protected function createLoggerService() {
        $logger = $this->createLogger('default');

        $site = $this->get('siteManager')->getCurrentSite();
        if ($site->isDebug()) {
            $logger->pushHandler(
                $this->createStreamHandlerForLogger(
                    $site->getLogDirPath() . '/' . $site->getMode() . '-debug.log',
                    Logger::DEBUG
                )
            );
        }

        return $logger;
    }

    public function createUserManagerService() {
        return new UserManager($this->get('db'), $this->get('session'));
    }

    protected function isFallbackMode() {
        return $this->get('siteManager')->isFallbackMode();
    }

    protected function createStreamHandlerForLogger($filePath, $logLevel) {
        $handler = new StreamHandler($filePath, $logLevel);
        $handler->setFormatter(
            new LineFormatter(LineFormatter::SIMPLE_FORMAT . "-------------------------------------------------------------------------------\n", null, true)
        );
        return $handler;
    }

    protected function createLogger(string $name): Logger {
        return (new Logger($name))
            ->pushProcessor(new WebProcessor())
            ->pushProcessor(new MemoryUsageProcessor())
            ->pushProcessor(new MemoryPeakUsageProcessor())
            ->pushProcessor(new IntrospectionProcessor());
    }
}
