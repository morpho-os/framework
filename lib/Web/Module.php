<?php //declare(strict_types=1);
namespace Morpho\Web;

use const Morpho\Core\CONTROLLER_SUFFIX;
/*use const Morpho\Core\DOMAIN_NS;
use const Morpho\Core\REPO_SUFFIX;*/

class Module extends Node {
    /**
     * @var ?string
     */
    protected $name;

    protected $type = 'Module';

    /**
     * @var ModulePathManager
     */
    protected $pathManager;

    public function __construct(string $name, ModulePathManager $pathManager) {
        parent::__construct($name);
        $this->pathManager = $pathManager;
    }

    public function setPathManager(ModulePathManager $pathManager): void {
        $this->pathManager = $pathManager;
    }

    public function pathManager(): ModulePathManager {
        return $this->pathManager;
    }

/*    public function repo($name) {
        return $this->offsetGet(DOMAIN_NS . '\\' . $name . REPO_SUFFIX);
    }*/

/*    protected function trigger(string $event, array $args = null) {
        return $this->parent('ModuleManager')
            ->trigger($event, $args);
    }*/

/*    protected function setSetting(string $name, $value, string $moduleName = null) {
        $this->serviceManager->get('settingsManager')
            ->set($name, $value, $moduleName ?: $this->name());
    }

    protected function setting(string $name, string $moduleName = null) {
        return $this->serviceManager->get('settingsManager')
            ->get($name, $moduleName ?: $this->name());
    }*/

    protected function childNameToClass(string $name) {
        if (false === strpos($name, '\\')) {
            //$name = (PHP_SAPI === 'cli' ? 'Cli' : 'Web') . '\\' . $name . CONTROLLER_SUFFIX;
            $name = 'Web\\' . $name . CONTROLLER_SUFFIX;
        }
        $moduleNs = $this->serviceManager->get('pathManager')->moduleNamespace($this->name());
        $class = $moduleNs . '\\' . $name;
        return class_exists($class) ? $class : false;
    }
}