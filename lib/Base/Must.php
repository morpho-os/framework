<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace Morpho\Base;

use InvalidArgumentException;
use OutOfRangeException;
use RuntimeException;
use function array_diff_key;
use function array_flip;
use function array_intersect_key;
use function array_keys;
use function count;
use function implode;

class Must {
    /**
     * @return mixed
     */
    public static function beNotEmpty(...$args) {
        $n = count($args);
        if (!$n) {
            throw new InvalidArgumentException("Empty arguments");
        }
        foreach ($args as $v) {
            self::beTrue(!empty($v), 'The value must be non empty');
        }
        return $n == 1 ? $args[0] : $args;
    }

    /**
     * @return mixed
     */
    public static function beEmpty(...$args) {
        $n = count($args);
        if (!$n) {
            throw new InvalidArgumentException("Empty arguments");
        }
        foreach ($args as $v) {
            self::beTrue(empty($v), 'The value must be empty');
        }
        return $n == 1 ? $args[0] : $args;
    }

    public static function haveOnlyKeys(array $arr, array $allowedKeys): void {
        $diff = array_diff_key($arr, array_flip($allowedKeys));
        if (count($diff)) {
            throw new RuntimeException('Not allowed items are present: ' . shorten(implode(', ', array_keys($diff)), 80));
        }
    }

    public static function haveKeys(array $arr, array $requiredKeys): void {
        $intersection = array_intersect_key(array_flip($requiredKeys), $arr);
        if (count($intersection) != count($requiredKeys)) {
            throw new RuntimeException("Required items are missing");
        }
    }

    public static function beInRange($value, $start, $end): void {
        if ($value < $start || $value > $end) {
            throw new OutOfRangeException("The value $value is out of range");
        }
    }

    /**
     * @param $val
     * @return mixed
     */
    public static function beNull($val) {
        self::beTrue($val === null);
        return $val;
    }

    /**
     * @param $val
     * @return mixed
     */
    public static function beNotNull($val) {
        self::beTrue($val !== null);
        return $val;
    }

    public static function beTrue($result, string $errMessage = null): void {
        $result = (bool)$result;
        if (false === $result) {
            throw new RuntimeException((string)$errMessage);
        }
    }

    /**
     * @param mixed $result
     * @return mixed
     */
    public static function beNotFalse($result, string $errMessage = null) {
        if (false === $result) {
            throw new RuntimeException((string)$errMessage);
        }
        return $result;
    }

    public static function contain($haystack, $needle, string $errMessage = null): void {
        self::beTrue(contains($haystack, $needle), $errMessage ?: 'A haystack does not contain a needle');
    }
}
