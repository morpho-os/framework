<?php
namespace Morpho\Web;

use Morpho\Base\Converter;
use Morpho\Base\Environment as BaseEnvironment;

class Environment extends BaseEnvironment {
    protected $startSession = false;

    public static function httpProtocolVersion(): string {
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
            if ($protocol === 'HTTP/1.1' || $protocol === 'HTTP/2.0' || $protocol === 'HTTP/1.0') {
                return $protocol;
            }
        }
        return 'HTTP/1.1';
    }

    public static function httpHost(): string {
        return isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
    }

    /**
     * Note that referrer is correct spelling and the referer is incorrect.
     */
    public static function httpReferrer(): string {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }

    /**
     * @TODO: Refactor this method.
     *
     * @return int|string Returns max upload file size in bytes or as string with suffix.
     */
    public static function getMaxUploadFileSize(bool $asBytes = true) {
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

    public function initServerVars(array $serverVars = []) {
        $_SERVER['HTTP_REFERER'] = self::httpReferrer();
        $_SERVER['SERVER_PROTOCOL'] = self::httpProtocolVersion();
        $_SERVER['HTTP_HOST'] = self::httpHost();

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
}
