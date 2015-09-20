<?php
declare(strict_types=1);

namespace Morpho\Core;

use Morpho\Di\IServiceManager;

abstract class Application {
    /**
     * @return mixed
     */
    public static function main(array $config = []) {
        $_this = new static($config);
        return $_this->returnResult(
            $_this->run()
        );
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
            return $this->handleFailure($e, $serviceManager ?? null);
        }
    }

    abstract protected function createServiceManager(): IServiceManager;

    /**
     * Handles exception and returns any value associated with it, it must be !== true.
     *
     * @return mixed
     */
    abstract protected function handleFailure(\Throwable $e, IServiceManager $serviceManager = null);

    /**
     * Returns result optionally applying some transformations. By default does't apply any transformation and
     * returns the result as is.
     *
     * @param mixed $result
     * @return mixed
     */
    protected function returnResult($result) {
        return $result;
    }
}
