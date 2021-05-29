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
     * Returns true if has a member with the given value.
     * @param string $val
     * @return bool
     */
    public static function hasVal(string $val): bool {
        foreach (static::members() as $val1) {
            if ($val1 === $val) {
                return true;
            }
        }
        return false;
    }

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

    /**
     * Return true if has a member with the given name.
     * @param string $name
     * @return bool
     */
    public static function hasName(string $name): bool {
        foreach (static::members() as $name1 => $_) {
            if ($name1 === $name) {
                return true;
            }
        }
        return false;
    }

    public static function vals(): array {
        return array_values(static::members());
    }

    public static function names(): array {
        return array_keys(static::members());
    }
}