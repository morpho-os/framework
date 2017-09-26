<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Di\IServiceManager;

abstract class Application {
    public static function main(IServiceManager $serviceManager = null) {
        $app = new static();
        return $app->run(null !== $serviceManager ? $serviceManager : $app->newServiceManager());
    }

    public function run(IServiceManager $serviceManager) {
        try {
            $this->configure($serviceManager);
            $request = $serviceManager->get('request');
            $serviceManager->get('router')->route($request);
            $serviceManager->get('dispatcher')->dispatch($request);
            $request->response()->send();
        } catch (\Throwable $e) {
            $this->logFailure($e, $serviceManager);
        }
    }

    protected function configure(IServiceManager $serviceManager): void {
        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();
    }

    abstract protected function logFailure(\Throwable $e, IServiceManager $serviceManager): void;
}
