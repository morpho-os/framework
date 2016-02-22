<?php
namespace Morpho\Core;

class Repo extends Node {
    protected function childNameToClass(string $name): string {
        return $name;
    }

    protected function getRepo(string $name, string $moduleName = null): Repo {
        if (null === $moduleName) {
            $module = $this->getParentByType('Module');
            $class = $this->getNamespace() . '\\' . $name . REPO_SUFFIX;
        } else {
            $module = $this->getParent('ModuleManager')->getChild($moduleName);
            $class = $module->getNamespace() . '\\' . DOMAIN_NS . '\\' . $name . REPO_SUFFIX;
        }
        return $module->getChild($class);
    }
}