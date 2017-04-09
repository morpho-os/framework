<?php
namespace Morpho\Code;

use ReflectionClass as BaseReflectionClass;

class ReflectionClass extends BaseReflectionClass {
    public function parentClasses(bool $appendSelf = true): array {
        $rClasses = [];
        $rClass = $this;
        while ($rClass = $rClass->getParentClass()) {
            $rClasses[] = $rClass;
        }
        $rClasses = array_reverse($rClasses);
        if ($appendSelf) {
            $rClasses[]= $this;
        }
        return $rClasses;
    }
}