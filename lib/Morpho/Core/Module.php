<?php
namespace Morpho\Core;

use Morpho\Db\Sql\Db;

abstract class Module extends Node {
    protected $name;

    protected $type = 'Module';

    public function __construct(array $options = []) {
        parent::__construct($options);
        $this->initClassLoader();
    }

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

    protected function childNameToClass(string $controllerName): string {
        if (false !== strpos($controllerName, '\\')) {
            return $controllerName;
        }
        return $this->getNamespace() . '\\' . CONTROLLER_NS . '\\' . $controllerName . CONTROLLER_SUFFIX;
    }

    protected function initClassLoader() {
        $classDirPath = $this->getClassDirPath();
        $autoloadFilePath = $classDirPath . COMPOSER_AUTOLOAD_FILE_PATH;
        if (is_file($autoloadFilePath)) {
            require $autoloadFilePath;
        }
    }
}
