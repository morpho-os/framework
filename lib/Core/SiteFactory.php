<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;
use Zend\Stdlib\ArrayUtils;

class SiteFactory {
    protected function loadMergedConfig(string $siteModuleName, array $siteConfig): \ArrayObject {
        require $siteConfig['paths']['dirPath'] . '/' . VENDOR_DIR_NAME . '/autoload.php';

        $configFilePath = $siteConfig['paths']['configFilePath'];
        $loadedConfig = ArrayUtils::merge($siteConfig, $this->loadConfigFile($configFilePath));

        if (!isset($loadedConfig['modules'])) {
            $loadedConfig['modules'] = [];
        }
        $newModules = [$siteModuleName => []]; // Store the site config as first item
        foreach ($loadedConfig['modules'] as $name => $moduleConfig) {
            if (is_numeric($name)) {
                $newModules[$moduleConfig] = [];
            } else {
                $newModules[$name] = $moduleConfig;
            }
        }
        $loadedConfig['modules'] = $newModules;

        return new \ArrayObject($loadedConfig);
    }

    protected function loadConfigFile(string $filePath) {
        if (!is_file($filePath)) {
            throw new \RuntimeException("Config file does not exist");
        }
        return require $filePath;
    }
}