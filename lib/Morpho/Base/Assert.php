<?php
namespace Morpho\Base;

class Assert {
    public static function fileReadable($filePath) {
        self::isTrue(is_file($filePath) && is_readable($filePath), "The file '$filePath' is not readable.");
    }

    public static function inArray($value, $array) {
        self::isTrue(in_array($value, $array, true), "Value '$value' was not found in array.");
    }

    public static function inRange($value, $start, $end) {
        if ($value < $start || $value > $end) {
            throw new \OutOfRangeException("The value $value is out of range.");
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
