<?php
namespace Morpho\Core;

use Morpho\Db\Sql\Db;

class Module extends Node {
    /**
     * @var ?string
     */
    protected $name;

    protected $type = 'Module';
    
    /**
     * @var string
     */
    private $dirPath;

    public function __construct(string $name, string $dirPath) {
        $this->name = $name;
        $this->dirPath = $dirPath;
    }

    public function setDirPath(string $dirPath): self {
        $this->dirPath = $dirPath;
        return $this;
    }

    public function dirPath(): string {
        return $this->dirPath;
    }

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

    public static function tableDefinitions(): array {
        return [];
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
        $moduleNs = $this->serviceManager->get('moduleFs')->moduleNamespace($this->name());
        $class = $moduleNs . '\\' . $name;
        return class_exists($class) ? $class : false;
    }
}