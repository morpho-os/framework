<?php
namespace Morpho\Core;

use Morpho\Di\IServiceManager;
use Morpho\Fs\Path;

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

    public static function detectBaseDirPath(string $dirPath = null, bool $throwEx = true): string {
        if (null === $dirPath) {
            $dirPath = __DIR__;
        }
        $rootDirPath = null;
        do {
            $path = $dirPath . '/vendor/composer/ClassLoader.php';
            if (is_file($path)) {
                $rootDirPath = $dirPath;
                break;
            } else {
                $chunks = explode(DIRECTORY_SEPARATOR, $dirPath, -1);
                $dirPath = implode(DIRECTORY_SEPARATOR, $chunks);
            }
        } while ($chunks);
        if (null === $rootDirPath) {
            if ($throwEx) {
                throw new \RuntimeException("Unable to find path of root directory.");
            }
            return null;
        }
        return Path::normalize($rootDirPath);
    }

    protected function init(IServiceManager $serviceManager) {
        $serviceManager->get('environment')->init();
        $serviceManager->get('errorHandler')->register();
    }

    abstract protected function createServiceManager(): IServiceManager;

    abstract protected function logFailure(\Throwable $e, IServiceManager $serviceManager = null);
}
