<?php
namespace Morpho\Base;

class Environment extends Object {
    const ENCODING = 'UTF-8';
    const TIMEZONE = 'UTC';

    protected $errorLevel = E_ALL | E_STRICT;

    protected $displayErrors = true;

    protected $isCliEnv = false;

    protected $serverVars = array();

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

        if (PHP_VERSION_ID < 50600) {
            die('PHP version must be >= 5.6');
        }

        if (PHP_SAPI === 'cli') {
            $this->initCli();
        }
        $this->initErrorLevel();
        $this->initDate();
        $this->initServerVars();
        $this->initSession();
        $this->initLocale();
        $this->initFs();

        static::$initialized = true;
    }

    public function initCli(array $options = []) {
        if ($this->isCliEnv && static::isCli()) {
            die('Access denied.');
        }
        $defaultOptions = array(
            'HTTP_HOST' => 'localhost',
            'SCRIPT_NAME' => null,
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_METHOD' => 'GET',
            'SERVER_NAME' => null,
            'SERVER_SOFTWARE' => null,
            'HTTP_USER_AGENT' => null,
            'SERVER_PROTOCOL' => 'HTTP/1.0',
        );
        $options += $defaultOptions;
        $_SERVER += $options;
    }

    public function initErrorLevel() {
        error_reporting($this->getErrorLevel());
        ini_set('display_errors', $this->displayErrors);
    }

    public function initDate() {
        ini_set('date.timezone', self::TIMEZONE);
    }

    public function initServerVars() {
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

        $defaults = array(
            'SCRIPT_NAME' => null,
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_METHOD' => 'GET',
            'SERVER_NAME' => null,
            'SERVER_SOFTWARE' => null,
            'HTTP_USER_AGENT' => null,
        );
        $_SERVER = $this->serverVars + $_SERVER + $defaults;
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

    public function initLocale() {
        //setlocale(LC_ALL, 'C');
        //$enc = self::ENCODING;
        ini_set('default_charset', self::ENCODING);
        // extension_loaded('mbstring') && mb_internal_encoding($enc);
        //iconv_set_encoding('internal_encoding', $enc); // Not actual since PHP_VERSION_ID >= 50600
    }

    public function initFs() {
        // @TODO: Ensure that we need do this.
        umask(0);
    }

    /**
     * @TODO: Refactore of this method.
     *
     * @param bool $asBytes
     * @return int|string Returns max upload file size in bytes or as string with suffix.
     */
    public function getMaxUploadFileSize($asBytes = true) {
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

    protected function getErrorLevel() {
        return $this->errorLevel;
    }
}
