<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Di\IServiceManager;
use Morpho\Error\ErrorHandler;

abstract class Application {
    /**
     * @var \ArrayAccess|array
     */
    protected $config;

    public function __construct($config = null) {
        $this->config = $config;
    }

    public static function main(array $config = null) {
        $app = new static($config);
        return $app->run();
    }

    /**
     * @return bool|IResponse
     */
    public function run() {
        try {
            $serviceManager = $this->newServiceManager();
            $this->configure($serviceManager);
            $request = $serviceManager->get('request');
            $serviceManager->get('router')->route($request);
            $serviceManager->get('dispatcher')->dispatch($request);
            return $request->response()->send();
        } catch (\Throwable $e) {
            $this->handleError($e, $serviceManager ?? null);
            return false;
        }
    }

    public function config() {
        return $this->config;
    }

    protected function handleError(\Throwable $e, ?IServiceManager $serviceManager): void {
        if ($serviceManager) {
            try {
                $serviceManager->get('errorHandler')->handleException($e);
            } catch (\Throwable $e) {
                $this->logErrorFallback($e);
            }
        } else {
            $this->logErrorFallback($e);
        }
        $this->showError($e);
    }

    protected function logErrorFallback(\Throwable $e): void {
        if (ErrorHandler::isErrorLogEnabled()) {
            // @TODO: check how error logging works on PHP core level, remove unnecessary calls and checks.
            error_log(addslashes((string)$e));
        }
    }

    abstract protected function newServiceManager(): IServiceManager;

    /**
     * Configures an application.
     */
    protected function configure(IServiceManager $serviceManager): void {
        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();
    }

    abstract protected function showError(\Throwable $e): void;
}