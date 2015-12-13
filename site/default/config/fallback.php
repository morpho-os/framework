<?php
return [
    'cacheDirPath' => __DIR__ . '/../cache',
    'db' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => '',
        'db' => '',
        'port' => '3306',
    ],
    'moduleAutoloader' => [
        'useCache' => false,
    ],
    'templateEngine' => [
        'useCache' => false,
    ],
    'serviceManager' => 'Morpho\Web\ServiceManager',
    'mode' => 'dev',
    'isDebug' => true,
];
