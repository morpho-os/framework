<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Base\ClassNotFoundException;
use Morpho\Base\Node as BaseNode;

class ModuleProvider extends Node {
    /**
     * @var ModuleIndex
     */
    protected $moduleIndex;

    /**
     * @var array
     */
    private $registeredModules = [];

    public function __construct(ModuleIndex $moduleIndex) {
        parent::__construct('ModuleProvider');
        $this->moduleIndex = $moduleIndex;
    }

    /**
     * @return string|false
     */
    protected function childNameToClass(string $name) {
        return $this->moduleIndex->moduleMeta($name)['class'];
    }

    protected function loadChild(string $moduleName): BaseNode {
        $class = $this->childNameToClass($moduleName);
        if (false === $class) {
            throw new ClassNotFoundException("Unable to load the module '$moduleName'");
        }
        $this->registerModuleAutoloader($moduleName);
        return new $class($moduleName, $this->moduleIndex);
    }

    private function registerModuleAutoloader(string $moduleName): void {
        if (!isset($this->registeredModules[$moduleName])) {
            // @TODO: Register simple autoloader, which must try to load the class using simple scheme, then call Composer's autoloader in case of fail.
            $moduleMeta = $this->moduleIndex->moduleMeta($moduleName);
            require $moduleMeta->autoloadFilePath();
            $this->registeredModules[$moduleName] = true;
        }
    }
}