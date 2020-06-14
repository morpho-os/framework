<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;
use Morpho\Base\IFn;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;
use Zend\Stdlib\ArrayUtils;

abstract class SiteFactory implements IFn, IHasServiceManager {
    protected IServiceManager $serviceManager;

    private const MAIN_MODULE = 'localhost';

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    public function __invoke($_ = null): ISite {
        var_dump('---1---');
        $hostName = $this->currentHostName();
        var_dump('---2---');
        if (!$hostName) {
            $this->throwInvalidSiteError();
        }
        var_dump('---3---');
        $initialSiteConfig = $this->hostNameToSiteModule($hostName);
        var_dump('---4---');
        if (false === $initialSiteConfig) {
            $this->throwInvalidSiteError();
        }
        var_dump('---5---');
        $siteConfig = $this->loadExtendedSiteConfig($initialSiteConfig['siteModule'], $initialSiteConfig);
        var_dump('---6---');
        return $this->mkSite($initialSiteConfig['siteModule'], $siteConfig, $hostName);
    }

    /**
     * @param string $hostName
     * @return array|false
     */
    protected function hostNameToSiteModule(string $hostName) {
        var_dump('HOSTNAME: ', $hostName);
        $allowedHostNames = ['localhost', 'framework', '127.0.0.1'];
        if (in_array($hostName, $allowedHostNames, true)) {
            $appConfig = $this->serviceManager['app']->config();
            $shortModuleName = self::MAIN_MODULE;
            $moduleDirPath = $appConfig['baseServerModuleDirPath'] . '/' . $shortModuleName;
            return [
                'siteModule' => VENDOR . '/' . $shortModuleName,
                'path' => [
                    'dirPath' => $moduleDirPath,
                    'configFilePath' => $moduleDirPath . '/' . CONFIG_DIR_NAME . '/site.config.php',
                    'clientModuleDirPath' => $appConfig['baseClientModuleDirPath'] . '/' . $shortModuleName,
                ],
            ];
        }
        return false;
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
