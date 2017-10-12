<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Base\Environment;
use Morpho\Di\IServiceManager;
use Morpho\Error\ErrorHandler;

abstract class Application {
    public static function main(array $config = null): int {
        $app = new static();
        $res = $app->run((array)$config);
        return $res ? Environment::SUCCESS_CODE : Environment::FAILURE_CODE;
    }

    public function run(array $config): bool {
        try {
            $serviceManager = isset($config['serviceManager']) ? $config['serviceManager'] : $this->newServiceManager($config);
            $this->configure($serviceManager);
            $request = $serviceManager->get('request');
            $serviceManager->get('router')->route($request);
            $serviceManager->get('dispatcher')->dispatch($request);
            $request->response()->send();
            return true;
        } catch (\Throwable $e) {
            $this->handleError($e, $serviceManager ?? null);
            return false;
        }
    }

    abstract public function newServiceManager(array $services): IServiceManager;

    /**
     * Configures an application.
     */
    protected function configure(IServiceManager $serviceManager): void {
        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();
    }

    protected function handleError(\Throwable $e, ?IServiceManager $serviceManager): void {
        try {
            $serviceManager->get('errorHandler')->handleException($e);
        } catch (\Throwable $e) {
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

    abstract protected function showError(\Throwable $e): void;
}
