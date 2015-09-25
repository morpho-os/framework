<?php
declare(strict_types=1);

namespace Morpho\Core;

use Morpho\Di\IServiceManager;

abstract class Application {
    /**
     * @return mixed Returns true on success and any value !== true on error.
     */
    public static function main(array $config = []) {
        return (new static($config))
            ->run();
    }

    /**
     * @return mixed Returns true on success and any value !== true on error.
     */
    public function run() {
        try {
            $serviceManager = $this->createServiceManager();

            $serviceManager->get('environment')->init();

            $serviceManager->get('errorHandler')->register();

            $request = $serviceManager->get('request');

            $serviceManager->get('router')->route($request);

            $serviceManager->get('dispatcher')->dispatch($request);

            $request->getResponse()->send();

            return true;
        } catch (\Throwable $e) {
            $this->logFailure($e, $serviceManager ?? null);
        }
    }

    abstract protected function createServiceManager(): IServiceManager;

    abstract protected function logFailure(\Throwable $e, IServiceManager $serviceManager = null);
}
