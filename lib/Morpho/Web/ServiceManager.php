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
use Morpho\Web\Routing\FallbackRouter;
use Morpho\Web\Routing\FastRouter;
use Morpho\Web\View\Compiler;
use Morpho\Web\View\HtmlParserPost;
use Morpho\Web\View\HtmlParserPre;
use Morpho\Web\View\PhpTemplateEngine;
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
        $templateEngine->attach(new HtmlParserPre($this))
            ->attach(new Compiler())
            ->attach(new HtmlParserPost($this, $templateEngineConfig['forceCompileTs'], $templateEngineConfig['nodeBinDirPath']));
        return $templateEngine;
    }

    protected function createMessengerService() {
        return new Messenger();
    }

    protected function createModuleManagerService() {
        $this->get('moduleClassLoader')->register();
        $moduleManager = new ModuleManager($this->get('db'));
        $moduleManager->isFallbackMode($this->isFallbackMode());
        return $moduleManager;
    }

    protected function createErrorHandlerService() {
        $logger = $this->createLogger('error');

        if (ErrorHandler::isErrorLogEnabled()) {
            $logger->pushHandler(new ErrorLogHandler());
        }

        $config = $this->config;
        if ($config['mailOnError']) {
            $logger->pushHandler(
                new NativeMailerHandler($this->config['mailTo'], 'An error has occurred', Logger::NOTICE)
            );
        }

        if ($config['logToFile']) {
            $logger->addSiteLogDirWriter($logger, 'error', Logger::DEBUG);
        }

        $listeners = [];
        $listeners[] = new NoDupsListener(new LogListener($logger));
        if ($config['addDumpListener']) {
            $listeners[] = new DumpListener();
        }

        return new ErrorHandler($listeners);
    }

    protected function createLoggerService() {
        $logger = $this->createLogger('default');
        $this->addSiteLogDirWriter($logger, 'debug', $this->logLevelToInt($logger, $this->config['logger']['logLevel']));
        return $logger;
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

    protected function addSiteLogDirWriter($logger, string $baseName, int $logLevel) {
        $site = $this->get('siteManager')->getCurrentSite();
        $logger->pushHandler(
            $this->createStreamHandlerForLogger(
                $site->getLogDirPath() . '/' . $baseName . '.log',
                $logLevel
            )
        );
    }

    protected function createLogger(string $name): Logger {
        return (new Logger($name))
            ->pushProcessor(new WebProcessor())
            ->pushProcessor(new MemoryUsageProcessor())
            ->pushProcessor(new MemoryPeakUsageProcessor())
            ->pushProcessor(new IntrospectionProcessor());
    }

    protected function logLevelToInt($logger, string $level): int {
        return $logger->getLevels()[strtoupper($level)];
    }
}
