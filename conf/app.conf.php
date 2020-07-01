<?php declare(strict_types=1);
use Morpho\App;

$baseDirPath = realpath(__DIR__ . '/..');
return [
    'serviceManager' => function (App\App $app) {
        return PHP_SAPI === 'cli' ? new App\Cli\BootServiceManager() : new App\Web\BootServiceManager();
    },
    'baseDirPath' => $baseDirPath,
    'baseServerModuleDirPath' => $baseDirPath . '/' . App\SERVER_MODULE_DIR_NAME,
    'baseClientModuleDirPath' => $baseDirPath . '/' . App\CLIENT_MODULE_DIR_NAME,
];
