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
     * @var \ArrayObject
     */
    protected $config;

    public function __construct(\ArrayObject $config = null) {
        $this->config = $config ?? new \ArrayObject([]);
    }

    public static function main(\ArrayObject $config = null) {
        $app = new static($config);
        return $app->run();
    }

    /**
     * @return bool|IResponse
     */
    public function run() {
        try {
            $serviceManager = ErrorHandler::trackErrors(function () {
                return $this->init();
            });
            $request = $serviceManager->get('request');
            $serviceManager->get('router')->route($request);
            $serviceManager->get('dispatcher')->dispatch($request);
            return $request->response()->send();
        } catch (\Throwable $e) {
            $this->handleError($e, $serviceManager ?? null);
            return false;
        }
    }

    public function config(): \ArrayObject {
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

    abstract protected function init(): IServiceManager;

    abstract protected function showError(\Throwable $e): void;
}