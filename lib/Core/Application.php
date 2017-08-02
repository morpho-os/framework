<?php
namespace Morpho\Core;

use Morpho\Di\IServiceManager;

abstract class Application {
    public static function main(IServiceManager $serviceManager = null) {
        $app = new static();
        return $app->run(null !== $serviceManager ? $serviceManager : $app->newServiceManager());
    }

    public function run(IServiceManager $serviceManager) {
        try {
            $this->init($serviceManager);
            $request = $serviceManager->get('request');
            $serviceManager->get('router')->route($request);
            $serviceManager->get('dispatcher')->dispatch($request);
            $request->response()->send();
        } catch (\Throwable $e) {
            $this->logFailure($e, $serviceManager);
        }
    }

    protected function init(IServiceManager $serviceManager): void {
        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();
    }

    abstract protected function logFailure(\Throwable $e, IServiceManager $serviceManager): void;
}
