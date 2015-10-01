<?php
namespace Morpho\Web;

class HttpTool {
    const UNKNOWN_IP = 'Unknown IP';

    /**
     * @arg bool $checkProxy
     * @arg int|null $index
     * @return string|null
     */
    public static function getIp($checkProxy = true, $index = null) {
        if ($checkProxy) {
            return self::getProxyIp($index);
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
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
}
