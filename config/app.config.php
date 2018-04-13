<?php declare(strict_types=1);
use const Morpho\App\Core\{CONFIG_DIR_NAME, MODULE_DIR_NAME};
use const Morpho\App\Web\PUBLIC_DIR_NAME;

$baseDirPath = dirname(__DIR__);
return [
    'hostMapper' => function (string $hostName) use ($baseDirPath) {
        $hostNames = ['localhost', 'framework', '127.0.0.1'];
        if (in_array($hostName, $hostNames, true)) {
            $siteDirPath = $baseDirPath . '/' . MODULE_DIR_NAME . '/localhost';
            return [
                'module'  => \Morpho\App\Core\VENDOR . '/localhost',
                'paths' => [
                    'dirPath' => $siteDirPath,
                    'publicDirPath' => $baseDirPath . '/' . PUBLIC_DIR_NAME,
                    'configFilePath' => $siteDirPath . '/' . CONFIG_DIR_NAME . '/site.config.php',
                ],
                'hostNames' => $hostNames,
            ];
        }
        return false;
    },
    'baseDirPath' => $baseDirPath,
];
