<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Db\Sql\Db;

class Module extends Node {
    /**
     * @var ?string
     */
    protected $name;

    protected $type = 'Module';

    // @TODO: move to installer (begin)

    public function install(Db $db) {
    }

    public function uninstall(Db $db) {
    }

    public function enable(Db $db) {
    }

    public function disable(Db $db) {
    }

    public static function tableDefinitions(): array {
        return [];
    }

    // @TODO: move to Installer (end)

    public function repo($name) {
        return $this->offsetGet(DOMAIN_NS . '\\' . $name . REPO_SUFFIX);
    }

    protected function trigger(string $event, array $args = null) {
        return $this->parent('ModuleManager')
            ->trigger($event, $args);
    }

    protected function setSetting(string $name, $value, string $moduleName = null) {
        $this->serviceManager->get('settingsManager')
            ->set($name, $value, $moduleName ?: $this->name());
    }

    protected function setting(string $name, string $moduleName = null) {
        return $this->serviceManager->get('settingsManager')
            ->get($name, $moduleName ?: $this->name());
    }

    protected function childNameToClass(string $name) {
        if (false === strpos($name, '\\')) {
            // By default any child is Controller.
            $name = CONTROLLER_NS . '\\' . $name . CONTROLLER_SUFFIX;
        }
        $moduleNs = $this->serviceManager->get('fs')->moduleNamespace($this->name());
        $class = $moduleNs . '\\' . $name;
        return class_exists($class) ? $class : false;
    }
}