<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

class ModuleConfig extends Config {
    protected $siteConfig;
    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @param array|\ArrayAccess $siteConfig
     */
    public function __construct($pathManager, string $moduleName, $siteConfig) {
        parent::__construct($pathManager);
        $this->moduleName = $moduleName;
        $this->siteConfig = $siteConfig;
    }

    public function offsetExists($key): bool {
        return parent::offsetExists($key)
            || isset($this->siteConfig['modules'][$this->moduleName][$key]);
    }

    /**
     * @return mixed
     */
    public function offsetGet($key) {
        $this->init();
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        return $this->siteConfig['modules'][$this->moduleName][$key];
    }

    public function offsetUnset($key): void {
        parent::offsetUnset($key);
        unset($this->siteConfig['modules'][$this->moduleName][$key]);
    }

    protected function load() {
        $filePath = $this->pathManager->configFilePath();
        if (is_file($filePath)) {
            return require $filePath;
        }
        return [];
    }
}