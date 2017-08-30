<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

class Repo extends Node {
    protected function repo(string $name, string $moduleName = null): Repo {
        $module = $moduleName === null
            ? $this->parentByType('Module')
            : $this->parent('ModuleManager')->childByName($moduleName);
        return $module->repo($name);
    }
}