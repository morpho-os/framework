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
use RuntimeException;
use Zend\Stdlib\ArrayUtils;
use function is_file;
use function Morpho\Base\last;

abstract class SiteFactory implements IFn, IHasServiceManager {
    protected array $appConf;

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->appConf = $serviceManager['app']->conf();
    }

    public function __invoke($_ = null): ISite {
        $hostName = $this->currentHostName();
        foreach ($this->appConf['sites'] as $siteName => $siteConf) {
            if (in_array($hostName, $siteConf['hosts'], true)) {
                return $this->mkSite($siteName, $siteConf, $hostName);
            }
        }
        $this->throwInvalidSiteError();
    }

    protected function mkSite(string $siteName, array $siteConf, string $hostName): ISite {
        return new Site($siteName, $siteConf['module']['name'], $this->loadExtendedSiteConf($siteConf), $hostName);
    }

    /**
     * @throws RuntimeException
     */
    abstract protected function throwInvalidSiteError(): void;

    /**
     * @return string|false
     */
    abstract protected function currentHostName();

    protected function loadExtendedSiteConf(array $basicSiteConf): array {
        $siteModuleConf = $basicSiteConf['module'];

        // Site's config file can use site module's classes so enable autoloading for it.
        require $siteModuleConf['paths']['dirPath'] . '/' . VENDOR_DIR_NAME . '/autoload.php';
        $extendedSiteConf = $this->loadConfFile($siteModuleConf['paths']['confFilePath']);

        $siteModuleName = $siteModuleConf['name'];
        unset($siteModuleConf['name']);

        $normalizedModulesConf = [];
        foreach (ArrayUtils::merge([$siteModuleName => $siteModuleConf], $extendedSiteConf['modules']) as $moduleName => $moduleConf) {
            $shortModuleName = last($moduleName, '/');
            if (!isset($moduleConf['paths']['dirPath'])) {
                $moduleConf['paths']['dirPath'] = $extendedSiteConf['paths']['serverModuleDirPath'] . '/' . $shortModuleName;
            }
            if (!isset($moduleConf['paths']['clientModuleDirPath'])) {
                $moduleConf['paths']['clientModuleDirPath'] = $extendedSiteConf['paths']['clientModuleDirPath'] . '/' . $shortModuleName;
            }
            $normalizedModulesConf[$moduleName] = $moduleConf;
        }
        $extendedSiteConf['modules'] = $normalizedModulesConf;

        return $extendedSiteConf;
    }

    protected function loadConfFile(string $filePath): array {
        if (!is_file($filePath)) {
            throw new RuntimeException("Configuration file does not exist");
        }
        return require $filePath;
    }
}
