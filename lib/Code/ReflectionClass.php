<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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