<?php
use const Morpho\Core\MODULE_DIR_NAME;
use const Morpho\Web\PUBLIC_DIR_NAME;

$baseDirPath = dirname(__DIR__);
return [
    'hostMapper' => function (string $hostName) use ($baseDirPath) {
        $hostNames = ['localhost', 'framework', '127.0.0.1'];
        if (in_array($hostName, $hostNames, true)) {
            return [
                'module'  => \Morpho\Core\VENDOR . '/localhost',
                'paths' => [
                    'dirPath' => $baseDirPath . '/' . MODULE_DIR_NAME . '/localhost',
                    'publicDirPath' => $baseDirPath . '/' . PUBLIC_DIR_NAME,
                ],
                'hostNames' => $hostNames,
            ];
        }
        return false;
    },
    'baseDirPath' => $baseDirPath,
];