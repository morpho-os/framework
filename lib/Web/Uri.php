<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

/**
 * This class based on \Zend\Uri\Uri and \Zend\Uri\Http classes.
 * @see https://github.com/zendframework/zend-http for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license https://github.com/zendframework/zend-http/blob/master/LICENSE.md New BSD License
 */
class Uri {
    /**
     * @var ?string
     */
    protected $scheme;

    /**
     * @var ?string
     */
    private $authority;

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
        return $this->authority;
    }

    public function setHost(string $host): void {
        $this->host = $host;
    }

    public function host(): ?string {
        return $this->host;
    }

    public function setPort(int $port): void {
        $this->port = $port;
    }

    public function port(): ?int {
        return $this->port;
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
}