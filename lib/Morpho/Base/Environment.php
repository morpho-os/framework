<?php
namespace Morpho\Base;

abstract class Environment extends Object {
    const ENCODING = 'UTF-8';
    const TIMEZONE = 'UTC';

    protected $isCliEnv = false;

    protected $startSession = false;

    protected static $initialized = false;

    public static function isXdebugEnabled() {
        return (bool)ini_get('xdebug.default_enable');
    }

    public static function isCli() {
        return PHP_SAPI == 'cli';
    }

    public static function isWin() {
        return DIRECTORY_SEPARATOR == '\\';
    }

    public static function isUnix() {
        return DIRECTORY_SEPARATOR == '/';
    }

    /**
     * Returns true if the ini setting with the $name can be interpreted as true.
     */
    public static function isIniSet(string $name): bool {
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

    public function init() {
        if (static::$initialized) {
            throw new \RuntimeException("The environment was already initialized.");
        }

        //if (PHP_VERSION_ID < 70000) {
        $this->_init();

        static::$initialized = true;
    }

    protected function _init() {
        $this->initErrorSettings();
        $this->initDate();
        $this->initServerVars();
        $this->initLocale();
        $this->initFs();
    }

    protected function initErrorSettings() {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 0);
    }

    protected function initDate() {
        ini_set('date.timezone', self::TIMEZONE);
    }

    abstract protected function initServerVars();

    protected function initLocale() {
        //setlocale(LC_ALL, 'C');
        //$enc = self::ENCODING;
        ini_set('default_charset', self::ENCODING);
        // extension_loaded('mbstring') && mb_internal_encoding($enc);
        //iconv_set_encoding('internal_encoding', $enc); // Not actual since PHP_VERSION_ID >= 50600
    }

    protected function initFs() {
        // @TODO: Ensure that we need do this.
        umask(0);
    }
}
