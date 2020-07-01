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
        $hostName = $this->currentHostName();
        if (!$hostName) {
            $this->throwInvalidSiteError();
        }

        $initialSiteConf = $this->hostNameToSiteModule($hostName);
        if (false === $initialSiteConf) {
            $this->throwInvalidSiteError();
        }

        $siteConf = $this->loadExtendedSiteConf($initialSiteConf['siteModule'], $initialSiteConf);

        return $this->mkSite($initialSiteConf['siteModule'], $siteConf, $hostName);
    }

    /**
     * @param string $hostName
     * @return array|false
     */
    protected function hostNameToSiteModule(string $hostName) {
        $allowedHostNames = ['localhost', 'framework', '127.0.0.1'];
        if (in_array($hostName, $allowedHostNames, true)) {
            $appConf = $this->serviceManager['app']->conf();
            $shortModuleName = self::MAIN_MODULE;
            $moduleDirPath = $appConf['baseServerModuleDirPath'] . '/' . $shortModuleName;
            return [
                'siteModule' => VENDOR . '/' . $shortModuleName,
                'path' => [
                    'dirPath' => $moduleDirPath,
                    'confFilePath' => $moduleDirPath . '/' . CONF_DIR_NAME . '/site.conf.php',
                    'clientModuleDirPath' => $appConf['baseClientModuleDirPath'] . '/' . $shortModuleName,
                ],
            ];
        }
        return false;
    }

    protected function mkSite(string $siteModuleName, \ArrayObject $siteConf, string $hostName): ISite {
        return new Site($siteModuleName, $siteConf, $hostName);
    }

    /**
     * @throws \RuntimeException
     */
    abstract protected function throwInvalidSiteError(): void;

    /**
     * @return string|false
     */
    abstract protected function currentHostName();

    protected function loadExtendedSiteConf(string $siteModuleName, array $initialSiteConf): \ArrayObject {
        require $initialSiteConf['path']['dirPath'] . '/' . VENDOR_DIR_NAME . '/autoload.php';

        $confFilePath = $initialSiteConf['path']['confFilePath'];
        $extendedSiteConf = ArrayUtils::merge($initialSiteConf, $this->loadConfFile($confFilePath));

        if (!isset($extendedSiteConf['module'])) {
            $extendedSiteConf['module'] = [];
        }
        $newModules = [$siteModuleName => []]; // Store the site conf as first item
        foreach ($extendedSiteConf['module'] as $name => $moduleConf) {
            if (\is_numeric($name)) {
                $newModules[$moduleConf] = [];
            } else {
                $newModules[$name] = $moduleConf;
            }
        }
        $extendedSiteConf['module'] = $newModules;

        return new \ArrayObject($extendedSiteConf);
    }

    protected function loadConfFile(string $filePath): array {
        if (!\is_file($filePath)) {
            throw new \RuntimeException("Configuration file does not exist");
        }
        return require $filePath;
    }
}
