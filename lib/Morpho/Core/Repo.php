<?php
namespace Morpho\Core;

class Repo extends Node {
    protected function getRepo(string $name, string $moduleName = null): Repo {
        $module = $moduleName === null
            ? $this->getParentByType('Module')
            : $this->getParent('ModuleManager')->getChild($moduleName);
        return $module->getRepo($name);
    }
}