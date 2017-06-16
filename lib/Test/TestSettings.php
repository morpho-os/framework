<?php
declare(strict_types=1);
namespace Morpho\Test;

class TestSettings {
    private static $settings;

    public static function set(string $name, $value) {
        self::$settings[$name] = $value;
    }

    public static function get(string $name) {
        return self::$settings[$name];
    }
}