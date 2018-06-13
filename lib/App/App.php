<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Base\Environment;
use Morpho\Base\Event;
use Morpho\Base\EventManager;
use Morpho\Error\ErrorHandler;
use Morpho\Ioc\IServiceManager;

class App extends EventManager {
    /**
     * @var \ArrayObject
     */
    protected $config;

    public function __construct(\ArrayObject $config = null) {
        $this->setConfig($config ?: new \ArrayObject([]));
    }

    public static function main(\ArrayObject $config = null): int {
        try {
            $app = new static($config);
            $response = $app->run();
            $exitCode = $response ? Environment::SUCCESS_CODE : Environment::FAILURE_CODE;
            $event = new Event('exit', ['exitCode'=> $exitCode, 'response' => $response]);
            $app->trigger($event);
            return $event->args['exitCode'];
        } catch (\Throwable $e) {
            if (Environment::boolIniVal('display_errors')) {
                echo $e;
            }
            self::logErrorFallback($e);
        }
        return Environment::FAILURE_CODE;
    }

    /**
     * @return IResponse|false
     */
    public function run() {
        /** @var IServiceManager $serviceManager */
        $serviceManager = $this->config['serviceManager'];
        try {
            $serviceManager['app'] = $this;
            $initializer = $serviceManager['initializer'];
            /** @var \Morpho\Base\IInitializer $initializer */
            $initializer->init();
            /** @var IRequest $request */
            $request = $serviceManager['request'];
            $serviceManager['router']->route($request);
            $serviceManager['dispatcher']->dispatch($request);
            $response = $request->response();
            $response->send();
            return $response;
        } catch (\Throwable $e) {
            $errorHandler = $serviceManager['errorHandler'];
            $errorHandler->handleException($e);
            //$this->trigger(new Event('error', ['exception' => $e]));
            return false;
        }
    }

    public function setConfig(\ArrayObject $config): void {
        $this->config = $config;
    }

    public function config(): \ArrayObject {
        return $this->config;
    }

    protected static function logErrorFallback(\Throwable $e): void {
        if (ErrorHandler::isErrorLogEnabled()) {
            // @TODO: check how error logging works on PHP core level, remove unnecessary calls and checks.
            \error_log(\addslashes((string)$e));
        }
    }
}
