<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\Error\ErrorHandler;
use Morpho\App\Cli\AppInitializer as CliAppInitializer;
use Morpho\App\Web\AppInitializer as WebAppInitializer;

class App implements IHasServiceManager {
    /**
     * @var \ArrayObject
     */
    protected $config;
    /**
     * @var IServiceManager
     */
    protected $serviceManager;

    /**
     * @var IAppInitializer
     */
    protected $initializer;

    public function __construct(\ArrayObject $config = null) {
        $this->initializer = $this->mkInitializer($config);
        $this->setConfig($config ?: new \ArrayObject([]));
    }

    /**
     * @param \ArrayObject|null $config
     * @return IResponse|false
     */
    public static function main(\ArrayObject $config = null) {
        try {
            $app = new static($config);
            return $app->run();
        } catch (\Throwable $e) {
            if (\Morpho\Base\Environment::boolIniVal('display_errors')) {
                echo $e;
            }
            self::logErrorFallback($e);
            return false;
        }
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @return IResponse
     */
    public function run() {
        try {
            $serviceManager = $this->mkServiceManager();
            $this->init($serviceManager);
            /** @var IRequest $request */
            $request = $serviceManager['request'];
            $serviceManager['router']->route($request);
            $serviceManager['dispatcher']->dispatch($request);
            $response = $request->response();
            $response->send();
            return $response;
        } catch (\Throwable $e) {
            if (isset($serviceManager)) {
                $errorHandler = $serviceManager['errorHandler'];
                $errorHandler->handleException($e);
            } else {
                $errorHandler = $this->initializer->mkFallbackErrorHandler();
                $errorHandler($e);
            }
        }
    }

    public function setConfig(\ArrayObject $config): void {
        $this->config = $config;
    }

    public function config(): \ArrayObject {
        return $this->config;
    }

    public function serviceManager(): IServiceManager {
        if (null === $this->serviceManager) {
            $this->serviceManager = $this->mkServiceManager();
        }
        return $this->serviceManager;
    }

    protected static function logErrorFallback(\Throwable $e): void {
        if (ErrorHandler::isErrorLogEnabled()) {
            // @TODO: check how error logging works on PHP core level, remove unnecessary calls and checks.
            \error_log(\addslashes((string)$e));
        }
    }

    protected function mkServiceManager(): IServiceManager {
        $initializer = $this->initializer;
        $site = $initializer->mkSite($this->config);
        $services = [
            'app'  => $this,
            'site' => $site,
        ];
        /** @var IServiceManager $serviceManager */
        $serviceManager = $initializer->mkServiceManager($services);
        $serviceManager->setConfig($site->config()['service']);
        return $serviceManager;
    }

    protected function init(IServiceManager $serviceManager): void {
        $this->initializer->init($serviceManager);
    }

    protected function mkInitializer(\ArrayObject $config = null): IAppInitializer {
        if (isset($config['initializer'])) {
            $initializer = $config['initializer'];
        } else {
            $initializer = PHP_SAPI === 'cli' ? new CliAppInitializer() : new WebAppInitializer();
        }
        return $initializer;
    }
}
