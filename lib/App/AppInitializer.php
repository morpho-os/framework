<?php declare(strict_types=1);
namespace Morpho\App;

use Morpho\Ioc\IServiceManager;

abstract class AppInitializer implements IAppInitializer {
    public function init(IServiceManager $serviceManager): void {
        $siteConfig = $serviceManager['site']->config();
        if (isset($siteConfig['iniConfig'])) {
            $this->applyIniConfig($siteConfig['iniConfig']);
        }
        if (isset($siteConfig['umask'])) {
            \umask($siteConfig['umask']);
        }
    }

    protected function applyIniConfig(array $iniConfig, $parentName = null): void {
        foreach ($iniConfig as $name => $value) {
            $settingName = $parentName ? $parentName . '.' . $name : $name;
            if (\is_array($value)) {
                $this->applyIniConfig($value, $settingName);
            } else {
                \ini_set($settingName, $value);
            }
        }
    }
}
