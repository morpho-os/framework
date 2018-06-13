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
        $hostName = $this->currentHostName();
        if (!$hostName) {
            $this->throwInvalidSiteError();
        }
        
        $siteConfigProvider = $appConfig['siteConfigProvider'];
        $initialSiteConfig = $siteConfigProvider($hostName);
        if (!$initialSiteConfig) {
            $this->throwInvalidSiteError();
        }
        $siteModuleName = $initialSiteConfig['siteModule'];
        
        $siteConfig = $this->loadExtendedSiteConfig($siteModuleName, $initialSiteConfig);
        return $this->mkSite($siteModuleName, $siteConfig, $hostName);
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
    abstract protected function currentHostName();

    protected function loadExtendedSiteConfig(string $siteModuleName, array $initialSiteConfig): \ArrayObject {
        require $initialSiteConfig['path']['dirPath'] . '/' . VENDOR_DIR_NAME . '/autoload.php';

        $configFilePath = $initialSiteConfig['path']['configFilePath'];
        $extendedSiteConfig = ArrayUtils::merge($initialSiteConfig, $this->loadConfigFile($configFilePath));

        if (!isset($extendedSiteConfig['module'])) {
            $extendedSiteConfig['module'] = [];
        }
        $newModules = [$siteModuleName => []]; // Store the site config as first item
        foreach ($extendedSiteConfig['module'] as $name => $moduleConfig) {
            if (\is_numeric($name)) {
                $newModules[$moduleConfig] = [];
            } else {
                $newModules[$name] = $moduleConfig;
            }
        }
        $extendedSiteConfig['module'] = $newModules;

        return new \ArrayObject($extendedSiteConfig);
    }

    protected function loadConfigFile(string $filePath): array {
        if (!\is_file($filePath)) {
            throw new \RuntimeException("Config file does not exist");
        }
        return require $filePath;
    }
}
