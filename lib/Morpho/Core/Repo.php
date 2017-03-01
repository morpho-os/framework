<?php
namespace Morpho\Core;

class Repo extends Node {
    protected function repo(string $name, string $moduleName = null): Repo {
        $module = $moduleName === null
            ? $this->parentByType('Module')
            : $this->parent('ModuleManager')->childByName($moduleName);
        return $module->repo($name);
    }
}