<?php
use const Morpho\Core\MODULE_DIR_NAME;
use const Morpho\Web\PUBLIC_DIR_NAME;

$baseDirPath = dirname(__DIR__);
$baseModuleDirPath = $baseDirPath . '/' . MODULE_DIR_NAME;
return [
    'sites' => [
        'localhost' => [
            'module' => \Morpho\Core\VENDOR . '/localhost',
            'dirPath' => $baseModuleDirPath . '/localhost',
        ],
    ],
    'multiSiting' => false,
    'baseDirPath' => $baseDirPath,
    'baseModuleDirPath' => $baseModuleDirPath,
    'publicDirPath' => $baseDirPath . '/' . PUBLIC_DIR_NAME,
];
