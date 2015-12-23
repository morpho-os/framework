<?php
namespace Morpho\Core;

class Repo extends Node {
    protected function childNameToClass(string $name): string {
        return $name;
    }

    protected function getRepo(string $name): Repo {
        return $this->getParentByType('Module')
            ->get($this->getNamespace() . '\\' . $name . REPO_SUFFIX);
    }
}