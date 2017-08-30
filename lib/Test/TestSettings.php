<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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