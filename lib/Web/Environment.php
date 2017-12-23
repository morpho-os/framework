<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\Converter;
use Morpho\Base\Environment as BaseEnvironment;

class Environment extends BaseEnvironment {
    protected $startSession = false;

    public const PROTOCOL_VERSION = 'HTTP/1.1';
    
    public static function clientIp(): array {
        return [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            // http://nginx.org/en/docs/http/ngx_http_realip_module.html#real_ip_header
            'realIp' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
        ];
    }
    
    public static function httpProtocolVersion(): string {
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
            // preg_match('~^HTTP/\d+\.\d+$~si', $protocol)
            if ($protocol === 'HTTP/1.1' || $protocol === 'HTTP/2.0' || $protocol === 'HTTP/1.0') {
                return $protocol;
            }
        }
        return self::PROTOCOL_VERSION;
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
    public static function maxUploadFileSize(bool $asBytes = true) {
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

    protected function _init(): void {
        parent::_init();
        $_SERVER['HTTP_REFERER'] = self::httpReferrer();
        $_SERVER['SERVER_PROTOCOL'] = self::httpProtocolVersion();
        $_SERVER['HTTP_HOST'] = self::httpHost();
        $_SERVER += [
            'SCRIPT_NAME'     => null,
            'REMOTE_ADDR'     => null,
            'REQUEST_METHOD'  => 'GET',
            'SERVER_NAME'     => null,
            'SERVER_SOFTWARE' => null,
            'HTTP_USER_AGENT' => null,
        ];
    }
}
