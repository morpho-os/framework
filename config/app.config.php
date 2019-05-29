<?php declare(strict_types=1);
use Morpho\App;

return [
    'serviceManager' => function (App\App $app) {
        return PHP_SAPI === 'cli' ? new App\Cli\BootServiceManager() : new App\Web\BootServiceManager();
    },
    'baseDirPath' => dirname(__DIR__),
];
