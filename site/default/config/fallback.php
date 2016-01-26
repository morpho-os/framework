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
    'moduleClassLoader' => [
        'useCache' => false,
    ],
    'templateEngine' => [
        'useCache' => false,
        'forceCompileTs' => false,
        'nodeBinDirPath' => '/opt/nodejs/4.2.3/bin',
    ],
    'serviceManager' => 'Morpho\Web\ServiceManager',
    'mode' => 'dev',
    'isDebug' => true,
    'modules' => [
        'System',
        'User',
        'Bootstrap',
    ],
];
