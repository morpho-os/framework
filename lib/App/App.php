<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Base\Environment;
use Morpho\Base\Event;
use Morpho\Base\EventManager;
use Morpho\Error\ErrorHandler;
use Morpho\Ioc\IServiceManager;

class App extends EventManager {
    protected \ArrayObject $config;

    public function __construct(\ArrayObject $config = null) {
        $this->setConfig($config ?: new \ArrayObject([]));
    }

    public static function main(\ArrayObject $config = null): int {
        try {
            var_dump('before new');
            $app = new static($config);
            var_dump('before run');
            $response = $app->run();
            var_dump('after run');
            $exitCode = $response ? Environment::SUCCESS_CODE : Environment::FAILURE_CODE;
            $event = new Event('exit', ['exitCode'=> $exitCode, 'response' => $response]);
            var_dump('before exit');
            $app->trigger($event);
            return $event->args['exitCode'];
        } catch (\Throwable $e) {
            d($e);
            if (Environment::boolIniVal('display_errors')) {
                echo $e;
            }
            self::logErrorFallback($e);
        }
        return Environment::FAILURE_CODE;
    }

    /**
     * @return IResponse|false
     */
    public function run() {
        var_dump('before init()');
        $serviceManager = $this->init();
        var_dump('after init()');
        $site = $serviceManager['site'];
        var_dump('before site::invoke()');
        return $site->__invoke($serviceManager);
    }

    public function init(): IServiceManager {
        /** @var ServiceManager $serviceManager */
        $bootServiceManager = $this->config['serviceManager']($this);

        $bootServiceManager['app'] = $this;

        /** @var Site $site */
        $site = $bootServiceManager['site'];

        $serviceManager = $site->config()['serviceManager'];

        foreach ($bootServiceManager as $id => $service) {
            $serviceManager[$id] = $service;
        }

        $serviceManager->setConfig($site->config()['service']);

        /** @var AppInitializer $appInitializer */
        $appInitializer = $serviceManager['appInitializer'];
        $appInitializer->init();

        return $serviceManager;
    }

    public function setConfig(\ArrayObject $config): void {
        $this->config = $config;
    }

    public function config(): \ArrayObject {
        return $this->config;
    }

    protected static function logErrorFallback(\Throwable $e): void {
        if (ErrorHandler::isErrorLogEnabled()) {
            // @TODO: check how error logging works on PHP core level, remove unnecessary calls and checks.
            \error_log(\addslashes((string) $e));
        }
    }
}
