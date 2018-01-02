<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\Error\ErrorHandler;

abstract class Application implements IHasServiceManager {
    /**
     * @var \ArrayObject
     */
    protected $config;
    /**
     * @var IServiceManager
     */
    protected $serviceManager;

    public function __construct(\ArrayObject $config = null) {
        $this->setConfig($config ?: $this->newConfig());
        $this->init();
    }

    public static function main(\ArrayObject $config = null) {
        $app = new static($config);
        return $app->run();
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @return bool|IResponse
     */
    public function run() {
        try {
            $serviceManager = $this->serviceManager();
            /** @var Request $request */
            $request = $serviceManager->get('request');
            $serviceManager->get('router')->route($request);
            $serviceManager->get('dispatcher')->dispatch($request);
            $response = $request->response();
            $response->send();
            return $response;
        } catch (\Throwable $e) {
            $this->handleError($e, $serviceManager ?? null);
            return false;
        }
    }

    public function setConfig(\ArrayObject $config): void {
        $this->config = $config;
    }

    public function config(): \ArrayObject {
        return $this->config;
    }

    public function serviceManager(): IServiceManager {
        if (null === $this->serviceManager) {
            // Already initialized
            $this->serviceManager = $this->newServiceManager();
        }
        return $this->serviceManager;
    }

    protected function init(): void {
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

    protected function newConfig(): \ArrayObject {
        return new \ArrayObject([]);
    }

    abstract protected function showError(\Throwable $e): void;

    abstract protected function newServiceManager(): IServiceManager;
}