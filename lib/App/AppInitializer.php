<?php declare(strict_types=1);
namespace Morpho\App;

use Morpho\Ioc\IServiceManager;

abstract class AppInitializer {
    protected IServiceManager $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    abstract public function init(): void;

    protected function applySiteConf($siteConf): void {
        if (isset($siteConf['iniConf'])) {
            $this->applyIniConf($siteConf['iniConf']);
        }
    }

    protected function applyIniConf(array $iniConf, $parentName = null): void {
        foreach ($iniConf as $name => $value) {
            $settingName = $parentName ? $parentName . '.' . $name : $name;
            if (\is_array($value)) {
                $this->applyIniConf($value, $settingName);
            } else {
                \ini_set($settingName, $value);
            }
        }
    }
}
