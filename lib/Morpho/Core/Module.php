<?php
namespace Morpho\Core;

use Morpho\Db\Sql\Db;

class Module extends Node {
    protected $name;

    protected $type = 'Module';
    
    protected $moduleNamespace;

    public function install(Db $db) {
    }

    public function uninstall(Db $db) {
    }

    public function enable(Db $db) {
    }

    public function disable(Db $db) {
    }

    public function repo($name) {
        return $this->child(DOMAIN_NS . '\\' . $name . REPO_SUFFIX);
    }
    
    public function setModuleNamespace(string $namespace) {
        $this->moduleNamespace = $namespace;
    }

    /**
     * @return string|null
     */
    public function moduleNamespace() {
        return $this->moduleNamespace;
    }

    public static function tableDefinitions(): array {
        return [];
    }

    protected function trigger(string $event, array $args = null) {
        return $this->parent('ModuleManager')
            ->trigger($event, $args);
    }

    protected function setSetting(string $name, $value, string $moduleName = null) {
        $this->serviceManager->get('settingManager')
            ->set($name, $value, $moduleName ?: $this->name());
    }

    protected function setting(string $name, string $moduleName = null) {
        return $this->serviceManager->get('settingManager')
            ->get($name, $moduleName ?: $this->name());
    }

    protected function childNameToClass(string $name) {
        if (false === strpos($name, '\\')) {
            // By default any child is Controller.
            $name = CONTROLLER_NS . '\\' . $name . CONTROLLER_SUFFIX;
        }
        if (null !== $this->moduleNamespace) {
            $class = $this->moduleNamespace . '\\' . $name;
            return class_exists($class) ? $class : false;
        }
        return parent::childNameToClass($name);
    }
}