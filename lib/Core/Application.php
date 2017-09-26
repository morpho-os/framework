<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Di\IServiceManager;

abstract class Application {
    public static function main(array $config = null) {
        $app = new static();
        return $app->run((array)$config);
    }

    public function run(array $config) {
        try {
            $serviceManager = isset($config['serviceManager']) ? $config['serviceManager'] : $this->newServiceManager($config);
            $this->configure($serviceManager);
            $request = $serviceManager->get('request');
            $serviceManager->get('router')->route($request);
            $serviceManager->get('dispatcher')->dispatch($request);
            $request->response()->send();
        } catch (\Throwable $e) {
            $this->logFailure($e, $serviceManager);
        }
    }

    abstract public function newServiceManager(array $config): IServiceManager;

    protected function configure(IServiceManager $serviceManager): void {
        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();
    }

    abstract protected function logFailure(\Throwable $e, IServiceManager $serviceManager): void;
}
