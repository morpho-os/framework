<?php
use const Morpho\Core\MODULE_DIR_NAME;
use const Morpho\Web\PUBLIC_DIR_NAME;

$baseDirPath = dirname(__DIR__);
return [
    'sites' => [
        'localhost' => [
            'module' => \Morpho\Core\VENDOR . '/localhost',
            'dirPath' => $baseDirPath . '/' . MODULE_DIR_NAME . '/localhost',
        ],
    ],
    'multiSiting' => false,
    'baseDirPath' => $baseDirPath,
    'publicDirPath' => $baseDirPath . '/' . PUBLIC_DIR_NAME,
];
