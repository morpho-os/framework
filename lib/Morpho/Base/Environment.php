<?php
namespace Morpho\Base;

abstract class Environment extends Object {
    const ENCODING = 'UTF-8';
    const TIMEZONE = 'UTC';

    protected $isCliEnv = false;

    protected $startSession = false;

    protected static $initialized = false;
    
    public static function isXdebugEnabled(): bool {
        return (bool)ini_get('xdebug.default_enable');
    }

    public static function isCli(): bool {
        return PHP_SAPI == 'cli';
    }

    public static function isWindows(): bool {
        return DIRECTORY_SEPARATOR == '\\';
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
    public static function getBoolIni(string $name): bool {
        return self::iniToBool(ini_get($name));
    }
    
    /**
     * Converts any value that can be used in the ini configs to the bool value.
     */
    public static function iniToBool($value): bool {
        // Basic idea found here: php.net/ini_get.
        static $map = [
            // true values:
            'on'  => true, 'true' => true, 'yes' => true,
            // false values:
            'off' => false, 'false' => false, 'no' => false,
        ];
        return $map[strtolower($value)] ?? (bool)$value;
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
        Assert::isTrue(ini_get('zend.assertions') === '1', "The 'zend.assertions' ini option must be set to 1 for expectations");
        ini_set('assert.active', 1);
        ini_set('assert.exception', 1);
    }

    protected function _init()/*: void */ {
        $this->initErrorSettings();
        $this->initDate();
        $this->initServerVars();
        $this->initLocale();
        $this->initFs();
    }

    protected function initErrorSettings()/*: void */ {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 0);
    }

    protected function initDate()/*: void */ {
        ini_set('date.timezone', self::TIMEZONE);
    }

    abstract protected function initServerVars()/*: void */;

    protected function initLocale()/*: void */ {
        //setlocale(LC_ALL, 'C');
        //$enc = self::ENCODING;
        ini_set('default_charset', self::ENCODING);
        // extension_loaded('mbstring') && mb_internal_encoding($enc);
        //iconv_set_encoding('internal_encoding', $enc); // Not actual since PHP_VERSION_ID >= 50600
    }

    protected function initFs()/*: void */ {
        // @TODO: Ensure that we need do this.
        umask(0);
    }
}
