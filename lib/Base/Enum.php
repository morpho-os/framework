<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use ReflectionClass;

abstract class Enum {
    /**
     * Returns members of the enum - public constants.
     */
    public static function members(): array {
        $members = [];
        foreach ((new ReflectionClass(static::class))->getReflectionConstants() as $rConst) {
            if ($rConst->isPublic()) {
                $members[$rConst->getName()] = $rConst->getValue();
            }
        }
        return $members;
    }
}