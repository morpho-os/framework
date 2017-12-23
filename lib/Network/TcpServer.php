<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network;

class TcpServer {
    public static function isListening(Address $address): bool {
        $handle = @fsockopen('tcp://' . $address->host(), $address->port(), $errNo, $errStr, 1);
        if ($handle) {
            fclose($handle);
            return true;
        }
        return false;
    }
}