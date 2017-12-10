<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

class Uri {
    /**
     * @var ?string
     */
    protected $scheme;

    /**
     * @var ?string
     */
    private $userInfo;

    /**
     * @var ?string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var ?string
     */
    protected $path;

    /**
     * @var ?string
     */
    protected $query;

    /**
     * @var ?string
     */
    protected $fragment;

    /**
     * @var ?string
     */
    private $authority;

    public function __construct(string $uriStr = null) {
        if (null !== $uriStr) {
            $uri = (new UriParser())->__invoke($uriStr);
            $scheme = $uri->scheme();
            if (null !== $scheme) {
                $this->setScheme($scheme);
            }
            $authority = $uri->authority();
            if (null !== $authority) {
                $this->setAuthority($authority);
            }
            $path = $uri->path();
            if (null !== $path) {
                $this->setPath($path);
            }
            $query = $uri->query();
            if (null !== $query) {
                $this->setQuery($query);
            }
            $fragment = $uri->fragment();
            if (null !== $fragment) {
                $this->setFragment($fragment);
            }
        }
    }

    public function setScheme(string $scheme): void {
        $this->scheme = $scheme;
    }

    public function scheme() {
        return $this->scheme;
    }

    public function setAuthority(string $authority): void {
        $this->authority = $authority;
    }

    public function authority(): ?string {
        if (null === $this->authority) {
            $authority = (string)$this->userInfo;
            if ('' !== $authority) {
                $authority .= '@';
            }
            $authority .= $this->host;
            if (null !== $this->port) {
                $authority .= ':' . $this->port;
            }
            return $authority !== '' ? $authority : null;
        } else {
            return $this->authority;
        }
    }

    public function setUserInfo(string $userInfo): void {
        $this->userInfo = $userInfo;
    }

    public function userInfo(): ?string {
        if (null !== $this->userInfo) {
            return $this->userInfo;
        }
        if (null !== $this->authority) {
            $this->parseAuthority();
            return $this->userInfo;
        }
        return null;
    }

    public function setHost(string $host): void {
        $this->host = $host;
    }

    public function host(): ?string {
        if (null !== $this->host) {
            return $this->host;
        }
        if (null !== $this->authority) {
            $this->parseAuthority();
            return $this->host;
        }
        return null;
    }

    public function setPort(int $port): void {
        $this->port = $port;
    }

    public function port(): ?int {
        if (null !== $this->port) {
            return $this->port;
        }
        if (null !== $this->authority) {
            $this->parseAuthority();
            return $this->port;
        }
        return null;
    }

    public function setPath(string $path): void {
        $this->path = $path;
    }

    public function path(): ?string {
        return $this->path;
    }

    public function setQuery(string $query): void {
        $this->query = $query;
    }

    public function query(): ?string {
        return $this->query;
    }

    public function setFragment(string $fragment): void {
        $this->fragment = $fragment;
    }

    public function fragment(): ?string {
        return $this->fragment;
    }

    public static function parse(string $uri): self {
        return (new UriParser())->__invoke($uri);
    }

    public function __toString() {
        $uriStr = '';

        $scheme = $this->scheme();
        if ($scheme !== '') {
            $uriStr .= $scheme . ':';
        }

        $authority = $this->authority();
        if (null !== $authority) {
            $uriStr .= '//' . $authority;
        }

        $uriStr .= $this->path();

        $query = $this->query();
        if (null !== $query) {
            $uriStr .= '?' . $query;
        }

        $fragment = $this->fragment();
        if (null !== $fragment) {
            $uriStr .= '#' . $fragment;
        }

        return $uriStr;
    }

    private function parseAuthority(): void {
        $parts = (new UriParser())->parseOnlyAuthority($this->authority);
        $this->userInfo = $parts['userInfo'];
        $this->host = $parts['host'];
        $this->port = $parts['port'];
    }
}