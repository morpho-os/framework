<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network;

class Address {
    public $host;
    public $port;

    public function __construct(string $host, ?int $port) {
        $this->host = $host;
        $this->port = $port;
    }

    public static function fromString(string $address): self {
        $pos = strrpos($address, ':');
        if (false === $pos) {
            return new static($address, null);
        }
        $host = substr($address, 0, $pos);
        $port = substr($address, $pos + 1);
        return new static($host, (int)$port);
    }

    public function __toString() {
        return $this->host . (null !== $this->port ? ':' . $this->port : '');
    }
}