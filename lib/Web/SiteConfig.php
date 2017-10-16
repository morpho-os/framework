<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

class SiteConfig extends Config {
    /**
     * @return mixed
     */
    public function offsetGet($key) {
        $this->init();
        return $this->config[$key];
    }

    protected function load() {
        return $this->normalize(require $this->pathManager->configFilePath());
    }

    private function normalize(array $config): array {
        if (!isset($config['modules'])) {
            $config['modules'] = [];
        }
        foreach ($config['modules'] as $name => $conf) {
            if (is_numeric($name)) {
                $config['modules'][$conf] = [];
                unset($config['modules'][$name]);
            }
        }
        return $config;
    }
}