<?php
return [
    'cacheDirPath' => __DIR__ . '/../cache',
    'serviceManager' => 'Morpho\Web\ServiceManager',
    'db' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => '',
        'db' => '',
        'port' => '3306',
    ],
    'modules' => [
        'System',
        'User',
        'Bootstrap',
    ],
    'moduleAutoloader' => [
        'useCache' => false,
    ],
    'templateEngine' => [
        'useCache' => false,
        'forceCompileTs' => false,
        'nodeBinDirPath' => '/opt/nodejs/4.2.3/bin',
        'tsOptions' => [
            '--forceConsistentCasingInFileNames',
            '--removeComments',
            '--noImplicitAny',
            '--suppressImplicitAnyIndexErrors',
            '--noEmitOnError',
            '--newLine LF',
            '--allowJs',
        ],
    ],
    'errorHandler' => [
        'addDumpListener' => true,
    ],
    'errorLogger' => [
        'mailOnError' => false,
        'mailFrom' => 'admin@localhost',
        'mailTo' => 'admin@localhost',
        'logToFile' => true,
    ],
    'throwDispatchErrors' => false,
];
