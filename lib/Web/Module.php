<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use const Morpho\Web\CONTROLLER_SUFFIX;
/*use const Morpho\Web\DOMAIN_NS;
use const Morpho\Web\REPO_SUFFIX;*/

class Module extends Node {
    /**
     * @var ?string
     */
    protected $name;

    protected $type = 'Module';

    /**
     * @var \ArrayAccess|array
     */
    protected $config;

    /**
     * @var ModulePathManager
     */
    protected $pathManager;

    public function __construct(string $name, ModulePathManager $pathManager) {
        parent::__construct($name);
        $this->pathManager = $pathManager;
    }

    public function setPathManager(ModulePathManager $pathManager): void {
        $this->config = null;
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

    protected function childNameToClass(string $name) {
        if (false === strpos($name, '\\')) {
            //$name = (PHP_SAPI === 'cli' ? 'Cli' : 'Web') . '\\' . $name . CONTROLLER_SUFFIX;
            $name = 'Web\\' . $name . CONTROLLER_SUFFIX;
        }
        $moduleNs = $this->serviceManager->get('pathManager')->moduleNamespace($this->name());
        $class = $moduleNs . '\\' . $name;
        return class_exists($class) ? $class : false;
    }

    public function setConfig($config): void {
        $this->config = $config;
    }

    public function config() {
        if (null === $this->config) {
            $this->config = $this->newConfig();
        }
        return $this->config;
    }

    protected function newConfig() {
        return new ModuleConfig($this->pathManager, $this->name(), $this->serviceManager->get('site')->config());
    }
}