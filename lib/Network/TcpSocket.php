<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network;

class TcpSocket {
    public static function isListening(TcpAddress $address): bool {
        TcpAddress::check($address);
        $handle = @\fsockopen('tcp://' . $address->host(), $address->port(), $errNo, $errStr, 1);
        if ($handle) {
            \fclose($handle);
            return true;
        }
        return false;
    }

    public static function findFreePort(TcpAddress $address): TcpAddress {
        return TcpAddress::parse(\stream_socket_get_name(
            \stream_socket_server("tcp://{$address->host()}:0"), // :0 means bind random open port
            false
        ));
    }
}
