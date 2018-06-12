<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;
use Morpho\Base\IFn;
use Zend\Stdlib\ArrayUtils;

abstract class SiteFactory implements IFn {
    public function __invoke($appConfig): ISite {
        $hostName = $this->detectHostName();
        if (!$hostName) {
            $this->throwInvalidSiteError();
        }
        $siteConfigProvider = $appConfig['siteConfigProvider'];
        $siteConfig = $siteConfigProvider($hostName);
        if (!$siteConfig) {
            $this->throwInvalidSiteError();
        }
        $siteModuleName = $siteConfig['module'];
        unset($siteConfig['module']);
        return $this->mkSite($siteModuleName, $this->loadMergedConfig($siteModuleName, $siteConfig), $hostName);
    }

    protected function mkSite(string $siteModuleName, \ArrayObject $siteConfig, string $hostName): ISite {
        return new Site($siteModuleName, $siteConfig, $hostName);
    }

    /**
     * @throws \RuntimeException
     */
    abstract protected function throwInvalidSiteError(): void;

    /**
     * @return string|false
     */
    abstract protected function detectHostName();

    protected function loadMergedConfig(string $siteModuleName, array $siteConfig): \ArrayObject {
        require $siteConfig['path']['dirPath'] . '/' . VENDOR_DIR_NAME . '/autoload.php';

        $configFilePath = $siteConfig['path']['configFilePath'];
        $loadedConfig = ArrayUtils::merge($siteConfig, $this->loadConfigFile($configFilePath));

        if (!isset($loadedConfig['module'])) {
            $loadedConfig['module'] = [];
        }
        $newModules = [$siteModuleName => []]; // Store the site config as first item
        foreach ($loadedConfig['module'] as $name => $moduleConfig) {
            if (\is_numeric($name)) {
                $newModules[$moduleConfig] = [];
            } else {
                $newModules[$name] = $moduleConfig;
            }
        }
        $loadedConfig['module'] = $newModules;

        return new \ArrayObject($loadedConfig);
    }

    protected function loadConfigFile(string $filePath) {
        if (!\is_file($filePath)) {
            throw new \RuntimeException("Config file does not exist");
        }
        return require $filePath;
    }
}
