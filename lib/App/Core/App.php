<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Core;

use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\Error\ErrorHandler;

abstract class App implements IHasServiceManager {
    /**
     * @var \ArrayObject
     */
    protected $config;
    /**
     * @var IServiceManager
     */
    protected $serviceManager;

    public function __construct(\ArrayObject $config = null) {
        $this->setConfig($config ?: $this->newConfig());
        $this->init();
    }

    /**
     * @param \ArrayObject|null $config
     * @return IResponse|false
     */
    public static function main(\ArrayObject $config = null) {
        return static::safeMain($config);
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @return IResponse
     */
    public function run() {
        $serviceManager = $this->serviceManager();
        /** @var Request $request */
        $request = $serviceManager['request'];
        $serviceManager['router']->route($request);
        $serviceManager['dispatcher']->dispatch($request);
        $response = $request->response();
        $response->send();
        return $response;
    }

    public function setConfig(\ArrayObject $config): void {
        $this->config = $config;
    }

    public function config(): \ArrayObject {
        return $this->config;
    }

    public function serviceManager(): IServiceManager {
        if (null === $this->serviceManager) {
            $this->serviceManager = $this->newServiceManager();
        }
        return $this->serviceManager;
    }

    public function handleError(\Throwable $e): void {
        $serviceManager = $this->serviceManager;
        if ($serviceManager) {
            try {
                $serviceManager['errorHandler']->handleException($e);
            } catch (\Throwable $e) {
                static::logErrorFallback($e);
            }
        } else {
            static::logErrorFallback($e);
        }
        static::showError($e);
    }

    /**
     * @param \ArrayObject $config
     * @return IResponse|false
     */
    protected static function safeMain(\ArrayObject $config) {
        try {
            $app = new static($config);
            return $app->run();
        } catch (\Throwable $e) {
            if (isset($app)) {
                $app->handleError($e);
            } else {
                static::logErrorFallback($e);
                static::showError($e);
            }
            return false;
        }
    }

    protected function init(): void {
    }

    protected static function logErrorFallback(\Throwable $e): void {
        if (ErrorHandler::isErrorLogEnabled()) {
            // @TODO: check how error logging works on PHP core level, remove unnecessary calls and checks.
            \error_log(\addslashes((string)$e));
        }
    }

    protected function newConfig(): \ArrayObject {
        return new \ArrayObject([]);
    }

    abstract protected static function showError(\Throwable $e): void;

    protected function newServiceManager(): IServiceManager {
        $appConfig = $this->config;

        // factory can have a type: string (class name) | \Closure | IBootstrapFactory (instance)
        if (isset($appConfig['factory'])) {
            if (\is_object($appConfig['factory'])) {
                if ($appConfig['factory'] instanceof \Closure) {
                    $factory = $appConfig['factory']();
                } else {
                    // factory is IBootstrapFactory instance
                    $factory = $appConfig['factory'];
                }
            } else {
                // factory is a string containing a class name
                $factory = new $appConfig['factory'];
            }
        } else {
            $factory = $this->newBootstrapFactory();
        }

        $site = $factory->newSite($appConfig);

        $siteConfig = $site->config();

        if (isset($siteConfig['iniSettings'])) {
            $this->applyIniSettings($siteConfig['iniSettings']);
        }
        if (isset($siteConfig['umask'])) {
            \umask($siteConfig['umask']);
        }

        $services = [
            'app'  => $this,
            'site' => $site,
        ];
        /** @var ServiceManager $serviceManager */
        $serviceManager = $factory->newServiceManager($services);

        $serviceManager->setConfig($siteConfig['services']);

        return $serviceManager;
    }

    abstract protected function newBootstrapFactory(): IBootstrapFactory;

    protected function applyIniSettings(array $iniSettings, $parentName = null): void {
        foreach ($iniSettings as $name => $value) {
            $settingName = $parentName ? $parentName . '.' . $name : $name;
            if (\is_array($value)) {
                $this->applyIniSettings($value, $settingName);
            } else {
                \ini_set($settingName, $value);
            }
        }
    }
}
