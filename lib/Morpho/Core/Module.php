<?php
namespace Morpho\Core;

use Morpho\Db\Db;

abstract class Module extends Node {
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
        return $this->getChild(
            $this->getNamespace() . '\\' . DOMAIN_NS . '\\' . $name . REPO_SUFFIX
        );
    }

    protected function trigger($event, $target = null, $argv = null, $callback = null) {
        return $this->serviceManager->get('eventManager')->trigger($event, $target, $argv, $callback);
    }

    protected function nameToClass(string $name): string {
        if (false !== strpos($name, '\\')) {
            return $name;
        }
        return $this->getNamespace() . '\\' . CONTROLLER_NS . '\\' . $name . CONTROLLER_SUFFIX;
    }

    protected function setSetting($name, $value, $moduleName = null) {
        $this->serviceManager->get('settingManager')
            ->set($name, $value, $moduleName ?: $this->getName());
    }

    protected function getSetting($name, $moduleName = null) {
        return $this->serviceManager->get('settingManager')
            ->get($name, $moduleName ?: $this->getName());
    }
}
