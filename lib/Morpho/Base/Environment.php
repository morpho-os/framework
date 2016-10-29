<?php
namespace Morpho\Base;

abstract class Environment extends Object {
    const ENCODING = 'UTF-8';
    const TIMEZONE = 'UTC';

    protected static $initialized = false;

    public static function isXdebugEnabled(): bool {
        return self::getBoolIniVal('xdebug.default_enable');
    }

    public static function x64Arch(): bool {
        return PHP_INT_SIZE === 8;
    }

    public static function x32Arch(): bool {
        return PHP_INT_SIZE === 4;
    }

    public static function isCli(): bool {
        return PHP_SAPI == 'cli';
    }

    public static function isWindows(): bool {
        return defined('PHP_WINDOWS_VERSION_BUILD');//DIRECTORY_SEPARATOR == '\\';
    }

    public static function isUnix(): bool {
        return DIRECTORY_SEPARATOR == '/';
    }

    public static function isLinux(): bool {
        return self::isUnix() && !self::isMac();
    }

    public static function isMac(): bool {
        return false !== strpos(php_uname('s'), 'Darwin');
    }

    /**
     * Returns true if the ini setting with the $name can be interpreted as true.
     */
    public static function getBoolIniVal(string $name): bool {
        // @TODO: can we use just (bool) ini_get()?
        return self::iniValToBool(ini_get($name));
    }

    /**
     * Converts any value that can be used in the ini configs to the bool value.
     */
    public static function iniValToBool($value): bool {
        // Basic idea found here: php.net/ini_get.
        static $map = [
            // true values:
            'on'  => true, 'true' => true, 'yes' => true, '1' => true,
            // false values:
            'off' => false, 'false' => false, 'no' => false, 'none' => false, '' => false, '0' => false,
        ];
        return $map[strtolower($value)] ?? (bool)$value;
    }

    /**
     * Returns true if the ini-value looks like bool.
     */
    public static function isBoolLikeIniVal($value): bool {
        return in_array(strtolower($value), ['on', 'true', 'yes', '1', 1, 'off', 'false', 'none', '', '0', 0], true);
    }

    public function init()/*: void */ {
        if (static::$initialized) {
            throw new \RuntimeException("The environment was already initialized.");
        }

        //if (PHP_VERSION_ID < 70000) {
        $this->_init();

        static::$initialized = true;
    }

    public static function enableExpectations() {
        // http://php.net/assert#function.assert.expectations
        Must::beTrue(ini_get('zend.assertions') === '1', "The 'zend.assertions' ini option must be set to 1 for expectations");
        ini_set('assert.active', 1);
        ini_set('assert.exception', 1);
    }

    protected function _init()/*: void */ {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 0);
        ini_set('date.timezone', self::TIMEZONE);
        ini_set('default_charset', self::ENCODING);
        // @TODO: Ensure that we need do this.
        umask(0);
    }
}
