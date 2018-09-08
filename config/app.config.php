<?php declare(strict_types=1);
use const Morpho\App\{CONFIG_DIR_NAME, MODULE_DIR_NAME};
use const Morpho\App\Web\PUBLIC_DIR_NAME;

$isCli = PHP_SAPI === 'cli';
$baseDirPath = dirname(__DIR__);
return [
    'siteConfigProvider' => function (string $hostName) use ($baseDirPath) {
        $hostNames = ['localhost', 'framework', '127.0.0.1'];
        if (in_array($hostName, $hostNames, true)) {
            $moduleShortName = 'localhost';
            $siteDirPath = $baseDirPath . '/' . MODULE_DIR_NAME . '/' . $moduleShortName;
            return [
                'siteModule'  => \Morpho\App\VENDOR . '/' . $moduleShortName,
                'allowedHost' => $hostNames,
                'path' => [
                    'dirPath' => $siteDirPath,
                    'publicDirPath' => $baseDirPath . '/' . PUBLIC_DIR_NAME,
                    'configFilePath' => $siteDirPath . '/' . CONFIG_DIR_NAME . '/site.config.php',
                ],
            ];
        }
        return false;
    },
    'baseDirPath' => $baseDirPath,
    'serviceManager' => $isCli ? new \Morpho\App\Cli\ServiceManager() : new \Morpho\App\Web\ServiceManager(),
];
