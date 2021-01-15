<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Network;

use UnexpectedValueException;
use function strrpos;
use function substr;

class TcpAddress {
    protected string $host;
    protected ?int $port;

    public function __construct(string $host, ?int $port) {
        $this->host = $host;
        $this->port = $port;
    }

    public static function parse(string $address): self {
        $pos = strrpos($address, ':');
        if (false === $pos) {
            return new static($address, null);
        }
        $host = substr($address, 0, $pos);
        $port = substr($address, $pos + 1);
        return new static($host, (int)$port);
    }

    public static function check(TcpAddress $address): TcpAddress {
        if (!$address->host() || !$address->port()) {
            if (!$address->port() != 0) {
                throw new UnexpectedValueException();
            }
        }
        return $address;
    }

    public function setHost(string $host): void {
        $this->host = $host;
    }

    public function host(): string {
        return $this->host;
    }

    public function setPort(int $port): void {
        $this->port = $port;
    }

    public function port(): ?int {
        return $this->port;
    }

    public function __toString(): string {
        return $this->host . (null !== $this->port ? ':' . $this->port : '');
    }
}
