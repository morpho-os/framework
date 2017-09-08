<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Test;

class TestSettings {
    private static $values = [];

    private static $default = [
        'siteUri' => 'http://framework'
    ];

    public static function set(string $name, $value) {
        self::$values[$name] = $value;
    }

    public static function get(string $name) {
        if (!array_key_exists($name, self::$values[$name])) {
            return self::$default[$name];
        }
        return self::$values[$name];
    }

    public static function has(string $name): bool {
        return array_key_exists($name, self::$values) || array_key_exists($name, self::$default);
    }
}