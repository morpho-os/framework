<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Base\IFn;
use RuntimeException;
use Zend\Stdlib\ArrayUtils;
use function is_file;
use function Morpho\Base\last;

class SiteFactory implements IFn {
    protected IHostNameValidator $hostNameValidator;
    protected array $appConf;

    public function __construct(IHostNameValidator $hostNameValidator, array $appConf) {
        $this->hostNameValidator = $hostNameValidator;
        $this->appConf = $appConf;
    }

    public function __invoke($_ = null): ISite {
        $hostName = $this->hostNameValidator->currentHostName();
        foreach ($this->appConf['sites'] as $siteName => $siteConf) {
            if ($this->hostNameValidator->isValid($hostName)) {
                return $this->mkSite($siteName, $siteConf, $hostName);
            }
        }
        $this->hostNameValidator->throwInvalidSiteError();
    }

    protected function mkSite(string $siteName, array $siteConf, string $hostName): ISite {
        return new Site($siteName, $siteConf['module']['name'], $this->loadExtendedSiteConf($siteConf), $hostName);
    }

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
