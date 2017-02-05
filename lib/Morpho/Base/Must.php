<?php
namespace Morpho\Base;

class Must {
    /**
     * @return mixed
     */
    public static function beNotEmpty(...$args) {
        if (!count($args)) {
            throw new \InvalidArgumentException("Empty arguments");
        }
        foreach ($args as $v) {
            self::beTrue(!empty($v), 'The value must be non empty');
        }
        return count($args) == 1 ? $args[0] : $args;
    }

    /**
     * @return mixed
     */
    public static function beEmpty(...$args) {
        if (!count($args)) {
            throw new \InvalidArgumentException("Empty arguments");
        }
        foreach ($args as $v) {
            self::beTrue(empty($v), 'The value must be empty');
        }
        return count($args) == 1 ? $args[0] : $args;
    }

    public static function beOneOf($needle, array $haystack)/*: void */ {
        self::beTrue(in_array($needle, $haystack, true), 'The value is not one of the provided values');
    }

    public static function haveOnlyKeys(array $arr, array $allowedKeys)/*: void */ {
        $diff = array_diff_key($arr, array_flip($allowedKeys));
        if (count($diff)) {
            throw new \RuntimeException('Not allowed items are present: ' . shorten(implode(', ', array_keys($diff)), 80));
        }
    }

    public static function haveKeys(array $arr, array $requiredKeys)/*: void */ {
        $intersection = array_intersect_key(array_flip($requiredKeys), $arr);
        if (count($intersection) != count($requiredKeys)) {
            throw new \RuntimeException("Required items are missing");
        }
    }

    public static function beInArray($value, $array)/*: void */ {
        self::beTrue(in_array($value, $array, true), "Value '$value' was not found in array");
    }

    public static function beInRange($value, $start, $end)/*: void */ {
        if ($value < $start || $value > $end) {
            throw new \OutOfRangeException("The value $value is out of range");
        }
    }

    public static function beNotNull($value)/*: void */ {
        self::beTrue($value !== null);
    }

    public static function beTrue($result, string $errMessage = null)/*: void */ {
        $result = (bool)$result;
        if ($result === false) {
            throw new \RuntimeException($errMessage);
        }
    }
}
