<?php
namespace Morpho\Base;

class Environment extends Object {
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

    public function init() {
        if (static::$initialized) {
            throw new \RuntimeException("The environment was already initialized.");
        }

        /*
        if (PHP_VERSION_ID < 70000) {
            die('PHP version must be >= 5.6');
        }
        */

        $this->initErrorSettings();
        $this->initDate();
        $this->initServerVars();
        $this->initSession();
        $this->initLocale();
        $this->initFs();

        static::$initialized = true;
    }

    public function initErrorSettings() {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 0);
    }

    public static function initDate() {
        ini_set('date.timezone', self::TIMEZONE);
    }

    public static function initServerVars() {
        if (static::isCli()) {
            static::initServerVarsForCli();
        } else {
            static::initServerVarsForWeb();
        }
    }

    public static function initServerVarsForCli(array $serverVars = []) {
        $defaultServerVars = [
            'HTTP_HOST'       => 'localhost',
            'SCRIPT_NAME'     => null,
            'REMOTE_ADDR'     => '127.0.0.1',
            'REQUEST_METHOD'  => 'GET',
            'SERVER_NAME'     => null,
            'SERVER_SOFTWARE' => null,
            'HTTP_USER_AGENT' => null,
            'SERVER_PROTOCOL' => 'HTTP/1.0',
            'REQUEST_URI'     => '',
        ];
        $_SERVER += $serverVars + $defaultServerVars;
    }

    public static function initServerVarsForWeb(array $serverVars = []) {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            $_SERVER['HTTP_REFERER'] = '';
        }
        if (!isset($_SERVER['SERVER_PROTOCOL'])
            || ($_SERVER['SERVER_PROTOCOL'] !== 'HTTP/1.0' && $_SERVER['SERVER_PROTOCOL'] !== 'HTTP/1.1')
        ) {
            $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
        } else {
            $_SERVER['HTTP_HOST'] = '';
        }

        $defaultServerVars = [
            'SCRIPT_NAME'     => null,
            'REMOTE_ADDR'     => '127.0.0.1',
            'REQUEST_METHOD'  => 'GET',
            'SERVER_NAME'     => null,
            'SERVER_SOFTWARE' => null,
            'HTTP_USER_AGENT' => null,
        ];
        $_SERVER += $serverVars + $defaultServerVars;
    }

    public function initSession() {
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cache_limiter', '');
        ini_set('session.cookie_httponly', '1');

        if ($this->startSession) {
            if (isset($_SESSION) || defined('SID') || session_id()) {
                // Session is already started.
                return;
            }
            if (headers_sent()) {
                throw new \RuntimeException("Unable to start session: headers were already sent.");
            }
            session_start();
        }
    }

    public static function initLocale() {
        //setlocale(LC_ALL, 'C');
        //$enc = self::ENCODING;
        ini_set('default_charset', self::ENCODING);
        // extension_loaded('mbstring') && mb_internal_encoding($enc);
        //iconv_set_encoding('internal_encoding', $enc); // Not actual since PHP_VERSION_ID >= 50600
    }

    public static function initFs() {
        // @TODO: Ensure that we need do this.
        umask(0);
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

    /**
     * @TODO: Refactor this method.
     *
     * @param bool $asBytes
     * @return int|string Returns max upload file size in bytes or as string with suffix.
     */
    public static function getMaxUploadFileSize($asBytes = true) {
        $maxSizeIni = ini_get('post_max_size');
        $maxSize = Converter::toBytes($maxSizeIni);
        $uploadMaxSizeIni = ini_get('upload_max_filesize');
        $uploadMaxSize = Converter::toBytes($uploadMaxSizeIni);
        if ($uploadMaxSize > 0 && $uploadMaxSize < $maxSize) {
            $maxSize = $uploadMaxSize;
            $maxSizeIni = $uploadMaxSizeIni;
        }
        return $asBytes ? $maxSize : $maxSizeIni;
    }
}
