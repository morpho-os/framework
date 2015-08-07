<?php
namespace Morpho\Web;

class HttpTool {
    const UNKNOWN_IP = 'unknown';

    /**
     * @arg bool $checkProxy
     * @arg int|null $index
     * @return string
     */
    public static function getIp($checkProxy = true, $index = null) {
        if ($checkProxy) {
            $ip = self::getProxyIp($index);
        } else {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        }
        if (empty($ip)) {
            throw new \RuntimeException("Unable to detect of the IP address.");
        }
        return $ip;
    }

    /**
     * @arg int|null $index Index can be positive in the range [0..count($ips) - 1],
     *                        or negative in the range [-count($ips)..-1], in this case -1
     *                        corresponds to the last ip in the list and the -count($ips)
     *                        corresponds to the first ip in the list.
     * @return null|string
     */
    public static function getProxyIp($index) {
        $ip = !empty($_SERVER['HTTP_X_FORWARDED_FOR'])
            ? $_SERVER['HTTP_X_FORWARDED_FOR']
            : null;
        if (!$ip) {
            return null;
        }
        if (null !== $index) {
            $ips = array_map('trim', explode(',', $ip));
            $count = count($ips);
            if ($index >= $count) {
                $index = $count - 1;
            } elseif ($index < 0) {
                $index = count($ips) + $index;
                if ($index < 0) {
                    $index = 0;
                }
            }

            return !empty($ips[$index]) ? $ips[$index] : null;
        }
        return $ip ? $ip : null;
    }

    public static function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    public static function argsToString(array $args, $parent = '') {
        $string = array();
        foreach ($args as $key => $value) {
            $key = ($parent ? $parent . '[' . rawurlencode($key) . ']' : rawurlencode($key));

            if (is_array($value)) {
                $string[] = self::argsToString($value, $key);
            } elseif (!isset($value)) {
                $string[] = $key;
            } else {
                $string[] = $key . '=' . self::encodeUri($value);
            }
        }

        return implode('&', $string);
    }

    public static function stringToArgs($string) {
        $args = array();
        foreach (explode('&', $string) as $arg) {
            $arg = explode('=', $arg);
            $args[$arg[0]] = isset($arg[1]) ? rawurldecode($arg[1]) : '';
        }

        return $args;
    }

    public static function requestUri() {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            if (PHP_SAPI == 'cli' && isset($_SERVER['argv'][0])) {
                $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['argv'][0];
            } elseif (isset($_SERVER['QUERY_STRING'])) {
                $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
            } else {
                $uri = $_SERVER['SCRIPT_NAME'];
            }
        }

        return '/' . ltrim($uri, '/');
    }

    public static function encodeUri($uri) {
        return str_replace('%2F', '/', rawurlencode($uri));
    }
}
