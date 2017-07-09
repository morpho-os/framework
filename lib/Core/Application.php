<?php
namespace Morpho\Core;

use Morpho\Di\IServiceManager;

abstract class Application {
    public static function main(IServiceManager $serviceManager = null) {
        return (new static())
            ->run($serviceManager);
    }

    public function run(IServiceManager $serviceManager = null) {
        try {
            if (null === $serviceManager) {
                $serviceManager = $this->serviceManager();
            }

            $this->init($serviceManager);

            $request = $serviceManager->get('request');

            $serviceManager->get('router')->route($request);

            $serviceManager->get('dispatcher')->dispatch($request);

            $request->response()->send();
        } catch (\Throwable $e) {
            $this->logFailure($e);
        }
    }

    protected function init(IServiceManager $serviceManager): void {
        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();
    }

    abstract protected function serviceManager(): IServiceManager;

    abstract protected function logFailure(\Throwable $e): void;
}
