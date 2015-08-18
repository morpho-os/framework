<?php
declare(strict_types=1);

namespace Morpho\Core;

abstract class Application {
    public static function main(array $config = []): int {
        return (new static($config))->run();
    }

    /**
     * @return int Returns code == 0 on success and code != 0 on error.
     */
    public function run(): int {
        $exitCode = 0;
        try {
            $serviceManager = $this->createServiceManager();

            $serviceManager->get('environment')->init();

            $serviceManager->get('errorHandler')->register();

            $request = $serviceManager->get('request');

            $serviceManager->get('router')->route($request);

            $serviceManager->get('dispatcher')->dispatch($request);

            $request->getResponse()->send();
        } catch (\Throwable $e) {
            $exitCode = $this->handleErrorOrException($e, $serviceManager ?? null);
        }

        return $exitCode;
    }

    abstract protected function createServiceManager(): ServiceManager;

    abstract protected function handleErrorOrException(\Throwable $e, ServiceManager $serviceManager  = null): int;
}
