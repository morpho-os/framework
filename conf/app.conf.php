<?php declare(strict_types=1);
use Morpho\App;
use const Morpho\App\{CONF_DIR_NAME, SERVER_MODULE_DIR_NAME, VENDOR, SITE_CONF_FILE_NAME};

$baseDirPath = realpath(__DIR__ . '/..');
return [
    'serviceManager' => function (App\App $app) {
     },
    'sites' => [
        'localhost' => [
            'hosts' => ['localhost', 'framework', '127.0.0.1'],
            'module' => [
                'name' => VENDOR . '/localhost',
                'paths' => [
                    'dirPath' => $baseDirPath . '/' . SERVER_MODULE_DIR_NAME . '/localhost',
                    'confFilePath' => $baseDirPath . '/' . SERVER_MODULE_DIR_NAME . '/localhost/' . CONF_DIR_NAME . '/' . SITE_CONF_FILE_NAME,
                ],
            ],
        ],
    ],
];
