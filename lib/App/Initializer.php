<?php declare(strict_types=1);
namespace Morpho\App;

use Morpho\Base\IInitializer;
use Morpho\Ioc\IServiceManager;

abstract class Initializer implements IInitializer {
    /**
     * @var IServiceManager
     */
    protected $serviceManager;

    public function __construct(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    protected function applySiteConfig($siteConfig): void {
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
