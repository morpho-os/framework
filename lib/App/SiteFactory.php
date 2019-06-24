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
use const Morpho\App\Web\PUBLIC_DIR_NAME;

abstract class SiteFactory implements IFn, IHasServiceManager {
    /**
     * @var IServiceManager
     */
    protected $serviceManager;

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }

    public function __invoke($_ = null): ISite {
        $hostName = $this->currentHostName();
        if (!$hostName) {
            $this->throwInvalidSiteError();
        }

        $initialSiteConfig = $this->initialSiteConfig($hostName);
        if (!$initialSiteConfig) {
            $this->throwInvalidSiteError();
        }
        $siteModuleName = $initialSiteConfig['siteModule'];
        
        $siteConfig = $this->loadExtendedSiteConfig($siteModuleName, $initialSiteConfig);
        return $this->mkSite($siteModuleName, $siteConfig, $hostName);
    }

    /**
     * @param string $hostName
     * @return array|false
     */
    protected function initialSiteConfig(string $hostName) {
        $result = $this->hostNameToSiteModule($hostName);
        if (false !== $result) {
            return [
                'siteModule' => $result['moduleName'],
                'path'       => [
                    'dirPath'        => $result['moduleDirPath'],
                    'publicDirPath'  => $result['publicDirPath'],
                    'configFilePath' => $result['configFilePath'],
                ],
            ];
        }
        return false;
    }

    /**
     * @param string $hostName
     * @return array|false
     */
    protected function hostNameToSiteModule(string $hostName) {
        $allowedHostNames = ['localhost', 'framework', '127.0.0.1'];
        if (in_array($hostName, $allowedHostNames, true)) {
            $baseDirPath = $this->serviceManager['app']->config()['baseDirPath'];
            $shortModuleName = 'localhost';
            $moduleDirPath = $baseDirPath . '/' . MODULE_DIR_NAME . '/' . $shortModuleName;
            return [
                'moduleName' => VENDOR . '/' . $shortModuleName,
                'moduleDirPath' => $moduleDirPath,
                'publicDirPath' => $moduleDirPath . '/' . PUBLIC_DIR_NAME,
                'configFilePath' => $moduleDirPath . '/' . CONFIG_DIR_NAME . '/site.config.php',
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
