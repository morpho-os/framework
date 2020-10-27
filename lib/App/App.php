<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use ArrayObject;
use Morpho\Base\Env;
use Morpho\Base\Event;
use Morpho\Base\EventManager;
use Morpho\Error\ErrorHandler;
use Morpho\Ioc\IServiceManager;
use Throwable;
use function addslashes;
use function error_log;
use function umask;

class App extends EventManager {
    protected ArrayObject $conf;

    public function __construct(ArrayObject $conf = null) {
        $this->setConf($conf ?: new ArrayObject([]));
    }

    public static function main(ArrayObject $conf = null): int {
        try {
            $app = new static($conf);
            $response = $app->run();
            $exitCode = $response ? Env::SUCCESS_CODE : Env::FAILURE_CODE;
            $event = new Event('exit', ['exitCode'=> $exitCode, 'response' => $response]);
            $app->trigger($event);
            return $event->args['exitCode'];
        } catch (Throwable $e) {
            if (Env::boolIniVal('display_errors')) {
                echo $e;
            }
            self::logErrorFallback($e);
        }
        return Env::FAILURE_CODE;
    }

    /**
     * @return IResponse|false
     */
    public function run() {
        $serviceManager = $this->init();
        $site = $serviceManager['site'];
        return $site->__invoke($serviceManager);
    }

    public function init(): IServiceManager {
        /** @var ServiceManager $serviceManager */
        $bootServiceManager = $this->conf['serviceManager']($this);

        $bootServiceManager['app'] = $this;

        /** @var Site $site */
        $site = $bootServiceManager['site'];

        $siteConf = $site->conf();

        $serviceManager = $siteConf['serviceManager'];

        foreach ($bootServiceManager as $id => $service) {
            $serviceManager[$id] = $service;
        }

        $serviceManager->setConf($siteConf['service']);

        if (isset($siteConf['umask'])) {
            umask($siteConf['umask']);
        }

        /** @var AppInitializer $appInitializer */
        $appInitializer = $serviceManager['appInitializer'];
        $appInitializer->init();

        return $serviceManager;
    }

    public function setConf(ArrayObject $conf): void {
        $this->conf = $conf;
    }

    public function conf(): ArrayObject {
        return $this->conf;
    }

    protected static function logErrorFallback(Throwable $e): void {
        if (ErrorHandler::isErrorLogEnabled()) {
            // @TODO: check how error logging works on PHP core level, remove unnecessary calls and checks.
            error_log(addslashes((string) $e));
        }
    }
}
