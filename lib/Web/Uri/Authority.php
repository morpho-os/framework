<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Uri;

class Authority {
    /**
     * @var null|string
     */
    protected $userInfo;
    /**
     * @var null|string
     */
    protected $host;
    /**
     * @var int|null
     */
    protected $port;

    public function __construct(string $authority = null) {
        if (null !== $authority) {
            $authority = UriParser::parseOnlyAuthority($authority);
            $this->userInfo = $authority->userInfo;
            $this->host = $authority->host;
            $this->port = $authority->port;
        }
    }

    public function setUserInfo(?string $userInfo): void {
        $this->userInfo = $userInfo;
    }

    public function userInfo(): ?string {
        return $this->userInfo;
    }

    public function setHost(?string $host): void {
        $this->host = $host;
    }

    public function host(): ?string {
        return $this->host;
    }

    public function setPort(?int $port): void {
        $this->port = $port;
    }

    public function port(): ?int {
        return $this->port;
    }

    public function isNull(): bool {
        return null === $this->userInfo && null === $this->host && null === $this->port;
    }

    public function toStr(bool $encode = true) {
        // @TODO: Handle $encode
        $authority = (string)$this->userInfo;
        if ('' !== $authority) {
            $authority .= '@';
        }
        $authority .= $this->host;
        if (null !== $this->port) {
            $authority .= ':' . $this->port;
        }
        return $authority;//$authority !== '' ? $authority : null;
    }
}