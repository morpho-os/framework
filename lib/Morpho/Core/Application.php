<?php
namespace Morpho\Core;

use Morpho\Di\IServiceManager;

abstract class Application {
    protected $config = [];

    /**
     * @return mixed Returns true on success and any value !== true on error.
     */
    public static function main(array $config = []) {
        return (new static($config))
            ->run();
    }

    public function __construct(array $config = []) {
        $this->config = $config;
    }

    public function setConfig(array $config) {
        $this->config = $config;
        return $this;
    }

    public function config(): array {
        return $this->config;
    }

    /**
     * @return mixed Returns true on success and any value !== true on error.
     */
    public function run() {
        try {
            $serviceManager = $this->createServiceManager();

            $this->init($serviceManager);

            $request = $serviceManager->get('request');

            $serviceManager->get('router')->route($request);

            $serviceManager->get('dispatcher')->dispatch($request);

            $request->response()->send();

            return true;
        } catch (\Throwable $e) {
            $this->logFailure($e, $serviceManager ?? null);
        }
    }

    protected function init(IServiceManager $serviceManager) {
        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();
    }

    abstract protected function createServiceManager(): IServiceManager;

    abstract protected function logFailure(\Throwable $e, IServiceManager $serviceManager = null);
}
