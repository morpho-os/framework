<?php
namespace Morpho\Core;

use Morpho\Db\Sql\Db;

class Module extends Node {
    protected $name;

    protected $type = 'Module';

    public function install(Db $db) {
    }

    public function uninstall(Db $db) {
    }

    public function enable(Db $db) {
    }

    public function disable(Db $db) {
    }

    public function getRepo($name) {
        return $this->getChild(DOMAIN_NS . '\\' . $name . REPO_SUFFIX);
    }

    public static function getTableDefinitions(): array {
        return [];
    }

    protected function trigger(string $event, array $args = null) {
        return $this->getParent('ModuleManager')
            ->trigger($event, $args);
    }

    protected function setSetting(string $name, $value, string $moduleName = null) {
        $this->serviceManager->get('settingManager')
            ->set($name, $value, $moduleName ?: $this->getName());
    }

    protected function getSetting(string $name, string $moduleName = null) {
        return $this->serviceManager->get('settingManager')
            ->get($name, $moduleName ?: $this->getName());
    }

    protected function childNameToClass(string $name) {
        if (false === strpos($name, '\\')) {
            // By default any child is Controller.
            $name = CONTROLLER_NS . '\\' . $name . CONTROLLER_SUFFIX;
        }
        return parent::childNameToClass($name);
    }
}