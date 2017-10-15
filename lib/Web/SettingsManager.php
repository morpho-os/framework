<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\NotImplementedException;
use Morpho\Di\IHasServiceManager;
use Morpho\Di\IServiceManager;

class SettingsManager implements IHasServiceManager {
    private const NON_EXISTING = "\x01\x07851yQS8vSzS8nbAYftc"; // random string
    private $cache = [];
    private $serviceManager;

    /**
     * @return mixed
     */
    public function get(string $settingName, string $moduleName) {
        $val = $this->getOrDefault($settingName, $moduleName, self::NON_EXISTING);
        if ($val === self::NON_EXISTING) {
            throw new \RuntimeException("The setting '$settingName' does not exist for the module '$moduleName'");
        }
        return $val;
    }

    /**
     * @return mixed
     * @throws NotImplementedException
     */
    public function getOrDefault(string $settingName, string $moduleName, $default = null) {
        if (array_key_exists($settingName, $this->cache)) {
            return $this->cache[$settingName];
        }
        $moduleSettings = $this->serviceManager->get('site')->config()['modules'][$moduleName];
        if (!array_key_exists($settingName, $moduleSettings)) {
            $this->cache[$settingName] = self::NON_EXISTING;
            return $default;
        }
        // @TODO: Read a setting from a module config (config/config.php), if it does not exist then return the setting from the site's config.
        return $this->cache[$settingName] = $moduleSettings[$settingName];
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }
}