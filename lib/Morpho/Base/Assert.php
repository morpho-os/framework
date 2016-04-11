<?php
namespace Morpho\Base;

class Assert {
    public static function notEmpty(...$value) {
        foreach ($value as $v) {
            self::isTrue(!empty($v), 'The value is not empty');
        }
    }

    public static function isOneOf($needle, array $haystack) {
        self::isTrue(in_array($needle, $haystack, true), 'The value is not one of the provided values');
    }

    public static function hasOnlyKeys(array $arr, array $allowedKeys) {
        $diff = array_diff_key($arr, array_flip($allowedKeys));
        if (count($diff)) {
            throw new \RuntimeException('Not allowed items are present: ' . shorten(implode(', ', array_keys($diff)), 80));
        }
    }

    public static function hasKeys(array $arr, array $requiredKeys) {
        $intersection = array_intersect_key(array_flip($requiredKeys), $arr);
        if (count($intersection) != count($requiredKeys)) {
            throw new \RuntimeException("Required items are missing");
        }
    }

    public static function inArray($value, $array) {
        self::isTrue(in_array($value, $array, true), "Value '$value' was not found in array");
    }

    public static function inRange($value, $start, $end) {
        if ($value < $start || $value > $end) {
            throw new \OutOfRangeException("The value $value is out of range");
        }
    }

    public static function notNull($value) {
        self::isTrue($value !== null);
    }

    public static function isTrue($result, $message = null) {
        $result = (bool)$result;
        if ($result === false) {
            throw new \RuntimeException($message);
        }
    }
}
