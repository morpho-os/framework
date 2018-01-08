<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Cli;

use Morpho\Core\Application as BaseApplication;

abstract class Application extends BaseApplication {
    public static function main(\ArrayObject $config = null): int {
        $app = new static($config);
        $response = $app->run();
        if (false === $response) {
            return Environment::FAILURE_CODE;
        }
        return $response->statusCode();
    }

    protected function init(): void {
        Environment::init();
        $serviceManager = $this->serviceManager();
        $serviceManager->get('errorHandler')->register();
    }

    protected function showError(\Throwable $e): void {
        errorLn((string) $e);
    }
}