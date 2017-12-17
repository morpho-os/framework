<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use function Morpho\Base\startsWith;

/**
 * Implements of the [RFC 3986](https://tools.ietf.org/html/rfc3986)
 */
class Uri {
    /**
     * @var string
     */
    protected $scheme = '';

    /**
     * @var ?string
     */
    //private $userInfo;

    /**
     * @var ?string
     */
    //protected $host;

    /**
     * @var int
     */
    //protected $port;

    /**
     * @var string
     */
    protected $path = '';

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
    protected $basePath;

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

    /**
     * @param Authority|string|null $authority
     */
    public function setAuthority($authority): void {
        if (is_string($authority)) {
            $authority = new Authority($authority);
        }
        $this->authority = $authority;
    }

    public function authority(): ?Authority {
        return $this->authority;
    }

    public function setPath(string $path): void {
        $this->path = $path;
    }

    public function path(): string {
        return $this->path;
    }

    /**
     * @param string
     */
    public function setBasePath(string $path): void {
        $this->basePath = $path;
    }

    public function basePath(): ?string {
        return $this->basePath;
    }

    /**
     * @param Query|string|null $query
     */
    public function setQuery($query): void {
        if (is_string($query)) {
            $query = new Query($query);
        }
        $this->query = $query;
    }

    public function query(): ?Query {
        return $this->query;
    }

    public function setFragment(?string $fragment): void {
        $this->fragment = $fragment;
    }

    public function fragment(): ?string {
        return $this->fragment;
    }

    public static function parse(string $uri): self {
        return (new UriParser())->__invoke($uri);
    }

    /**
     * This method taken from https://github.com/zendframework/zend-uri/blob/master/src/Uri.php and changed to match our requirements.
     *
     * @link      http://github.com/zendframework/zf2 for the canonical source repository
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * Remove any extra dot segments (/../, /./) from a path
     *
     * Algorithm is adapted from RFC-3986 section 5.2.4
     * (@link http://tools.ietf.org/html/rfc3986#section-5.2.4)
     *
     * @TODO   consider optimizing
     */
    public static function removePathDotSegments(string $path): string {
        $output = '';

        while ($path) {
            if ($path == '..' || $path == '.') {
                break;
            }

            switch (true) {
                case ($path == '/.'):
                    $path = '/';
                    break;
                case ($path == '/..'):
                    $path   = '/';
                    $lastSlashPos = mb_strrpos($output, '/', -1);
                    if (false === $lastSlashPos) {
                        break;
                    }
                    $output = mb_substr($output, 0, $lastSlashPos);
                    break;
                case (mb_substr($path, 0, 4) == '/../'):
                    $path   = '/' . mb_substr($path, 4);
                    $lastSlashPos = mb_strrpos($output, '/', -1);
                    if (false === $lastSlashPos) {
                        break;
                    }
                    $output = mb_substr($output, 0, $lastSlashPos);
                    break;
                case (mb_substr($path, 0, 3) == '/./'):
                    $path = mb_substr($path, 2);
                    break;
                case (mb_substr($path, 0, 2) == './'):
                    $path = mb_substr($path, 2);
                    break;
                case (mb_substr($path, 0, 3) == '../'):
                    $path = mb_substr($path, 3);
                    break;
                default:
                    $slash = mb_strpos($path, '/', 1);
                    if ($slash === false) {
                        $seg = $path;
                    } else {
                        $seg = mb_substr($path, 0, $slash);
                    }

                    $output .= $seg;
                    $path    = mb_substr($path, mb_strlen($seg));
                    break;
            }
        }

        return $output;
    }

    /**
     * @param string|Uri
     */
    public function appended($relativeUri): Uri {
        if (is_string($relativeUri)) {
            $relativeUri = self::parse($relativeUri);
        }

        // Implementation of the [Reference Resolution](https://tools.ietf.org/html/rfc3986#section-5)

        /*
      -- A non-strict parser may ignore a scheme in the reference
      -- if it is identical to the base URI's scheme.
      --
      if ((not strict) and (R.scheme == Base.scheme)) then
         undefine(R.scheme);
      endif;
         */
        $targetUri = new Uri();
        $baseUri = $this;

        $scheme = $relativeUri->scheme();
        if ($scheme !== '') {
            $targetUri->setScheme($scheme);
            $targetUri->setAuthority($relativeUri->authority());
            $targetUri->setPath(self::removePathDotSegments($relativeUri->path()));
            $targetUri->setQuery($relativeUri->query());
        } else {
            $authority = $relativeUri->authority();
            if (null !== $authority) {
                $targetUri->setAuthority($authority);
                $targetUri->setPath(self::removePathDotSegments($relativeUri->path()));
                $targetUri->setQuery($relativeUri->query());
            } else {
                $path = $relativeUri->path();
                if ($path === '') {
                    // @TODO: Remove dot segments?
                    $targetUri->setPath($baseUri->path());
                    $query = $relativeUri->query();
                    if (null !== $query) {
                        $targetUri->setQuery($relativeUri->query());
                    } else {
                        $targetUri->setQuery($baseUri->query());
                    }
                } else {
                    if (startsWith($relativeUri->path, '/')) {
                        $targetUri->setPath(self::removePathDotSegments($relativeUri->path()));
                    } else {
                        // 5.2.3. Merge Paths.
                        $authority = $baseUri->authority();
                        $hasAuthority = null !== $authority;
                        $basePath = $baseUri->path();
                        if ($hasAuthority && $basePath === '') {
                            $targetPath = '/' . $relativeUri->path();
                        } else {
                            $rPos = mb_strrpos($basePath, '/');
                            if (false === $rPos) {
                                $targetPath = $relativeUri->path();
                            } else {
                                $targetPath = mb_substr($basePath, 0, $rPos + 1) . $relativeUri->path();
                            }
                        }
                        $targetPath = self::removePathDotSegments($targetPath);
                        $targetUri->setPath($targetPath);
                    }
                    $targetUri->setQuery($relativeUri->query());
                }
                $targetUri->setAuthority($baseUri->authority());
            }
            $targetUri->setScheme($baseUri->scheme());
        }
        $targetUri->setFragment($relativeUri->fragment());

        return $targetUri;
    }

    public function toString(bool $encode = true) {
        $uriStr = '';

        $scheme = $this->scheme();
        if ($scheme !== '') {
            $uriStr .= $scheme . ':';
        }

        $authority = $this->authority();
        if (null !== $authority) {
            $uriStr .= '//' . $authority->toString($encode);
        }

        $uriStr .= $this->path();

        $query = $this->query();
        if (null !== $query) {
            $uriStr .= '?' . $query->toString($encode);
        }

        $fragment = $this->fragment();
        if (null !== $fragment) {
            $uriStr .= '#' . $fragment;
        }

        return $uriStr;
    }
}