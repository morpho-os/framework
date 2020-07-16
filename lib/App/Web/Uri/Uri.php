<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Uri;

use Morpho\Base\Conf;
use function Morpho\Base\all;
use function Morpho\Base\lastPos;
use function Morpho\Base\startsWith;

/**
 * Implements the [RFC 3986](https://tools.ietf.org/html/rfc3986)
 */
class Uri {
    /**
     * @var string
     */
    protected $scheme = '';

    /**
     * @var ?Path
     */
    protected $path;

    /**
     * @var ?Query
     */
    protected $query;

    /**
     * @var ?string
     */
    protected $fragment;

    /**
     * @var ?Authority
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
            if (!$authority->isNull()) {
                $this->setAuthority($authority);
            }

            $path = $uri->path();
            if (null !== $path) {
                $this->setPath($path);
            }

            $query = $uri->query();
            if (!$query->isNull()) {
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
     * @param Authority|string $authority
     */
    public function setAuthority($authority): void {
        if (\is_string($authority)) {
            $authority = new Authority($authority);
        }
        $this->authority = $authority;
    }

    public function authority(): Authority {
        if (null === $this->authority) {
            $this->authority = new Authority();
        }
        return $this->authority;
    }

    /**
     * @param Path|string $path
     */
    public function setPath($path): void {
        if (\is_string($path)) {
            $path = new Path($path);
        }
        $this->path = $path;
    }

    public function path(): Path {
        if (null === $this->path) {
            $this->path = new Path('');
        }
        return $this->path;
    }

    /**
     * @param Query|string $query
     */
    public function setQuery($query): void {
        if (\is_string($query)) {
            $query = new Query($query);
        }
        $this->query = $query;
    }

    public function query(): Query {
        if (null === $this->query) {
            $this->query = new Query();
        }
        return $this->query;
    }

    public function setFragment(?string $fragment): void {
        $this->fragment = $fragment;
    }

    public function fragment(): ?string {
        return $this->fragment;
    }

    /**
     * @param string|Uri $uri
     * @return Uri
     */
    public static function parse($uri): self {
        if ($uri instanceof self) {
            return $uri;
        }
        return (new UriParser())->__invoke($uri);
    }

    /**
     * Implements of the [Reference Resolution](https://tools.ietf.org/html/rfc3986#section-5)
     * @param string|Uri $baseUri
     * @param string|Uri $relUri
     */
    public static function resolveRelUri($baseUri, $relUri): Uri {
        if (\is_string($baseUri)) {
            $baseUri = self::parse($baseUri);
        }
        if (\is_string($relUri)) {
            $relUri = self::parse($relUri);
        }

        /*
      -- A non-strict parser may ignore a scheme in the reference
      -- if it is identical to the base URI's scheme.
      --
      if ((not strict) and (R.scheme == Base.scheme)) then
         undefine(R.scheme);
      endif;
         */
        $targetUri = new Uri();

        $scheme = $relUri->scheme();
        if ($scheme !== '') {
            $targetUri->setScheme($scheme);
            $targetUri->setAuthority($relUri->authority());
            $targetUri->setPath(Path::removeDotSegments($relUri->path()->toStr(false)));
            $targetUri->setQuery($relUri->query());
        } else {
            $authority = $relUri->authority();
            if (!$authority->isNull()) {
                $targetUri->setAuthority($authority);
                $targetUri->setPath(Path::removeDotSegments($relUri->path()->toStr(false)));
                $targetUri->setQuery($relUri->query());
            } else {
                $path = $relUri->path()->toStr(false);
                if ($path === '') {
                    // @TODO: Remove dot segments?
                    $targetUri->setPath($baseUri->path());
                    $relUriQuery = $relUri->query();
                    if (!$relUriQuery->isNull()) {
                        $targetUri->setQuery($relUriQuery);
                    } else {
                        $targetUri->setQuery($baseUri->query());
                    }
                } else {
                    $relUriPath = $relUri->path()->toStr(false);
                    if (startsWith($relUriPath, '/')) {
                        $targetUri->setPath(Path::removeDotSegments($relUriPath));
                    } else {
                        // 5.2.3. Merge Paths.
                        $hasAuthority = !$baseUri->authority()->isNull();
                        $basePath = $baseUri->path()->toStr(false);
                        if ($hasAuthority && $basePath === '') {
                            $targetPath = '/' . $relUriPath;
                        } else {
                            $rPos = lastPos($basePath, '/');
                            if (false === $rPos) {
                                $targetPath = $relUriPath;
                            } else {
                                $targetPath = \mb_substr($basePath, 0, $rPos + 1) . $relUri->path()->toStr(false);
                            }
                        }
                        $targetPath = Path::removeDotSegments($targetPath);
                        $targetUri->setPath($targetPath);
                    }
                    $targetUri->setQuery($relUri->query());
                }
                $targetUri->setAuthority($baseUri->authority());
            }
            $targetUri->setScheme($baseUri->scheme());
        }
        $targetUri->setFragment($relUri->fragment());

        return $targetUri;
    }

    public function toStr(?array $parts, bool $encode): string {
        if (null !== $parts) {
            $conf = \array_fill_keys($parts, true);
            $conf = Conf::check([
                'scheme' => false,
                'authority' => false,
                'path' => false,
                'query' => false,
                'fragment' => false,
            ], $conf);
        } else {
            $conf = [
                'scheme' => true,
                'authority' => true,
                'path' => true,
                'query' => true,
                'fragment' => true,
            ];
        }

        $uriStr = '';

        $shouldReturnOnly = function (string $partName) use ($conf) {
            $val = $conf[$partName];
            unset($conf[$partName]);
            return $val && all(function ($val) {
                return !$val;
            }, $conf);
        };

        if ($conf['scheme']) {
            $scheme = $this->scheme();
            if ($scheme !== '') {
                if ($shouldReturnOnly('scheme')) {
                    return $encode ? \rawurlencode($scheme) : $scheme;
                }
                $uriStr .= ($encode ? \rawurlencode($scheme) : $scheme) . ':';
            }
        }

        if ($conf['authority']) {
            $authority = $this->authority();
            if (!$authority->isNull()) {
                if ($shouldReturnOnly('authority')) {
                    return $authority->toStr($encode);
                }
                $uriStr .= '//' . $authority->toStr($encode);
            }
        }

        if ($conf['path']) {
            $uriStr .= $this->path()->toStr($encode);
        }

        if ($conf['query']) {
            $query = $this->query();
            if (!$query->isNull()) {
                if ($shouldReturnOnly('query')) {
                    return $query->toStr($encode);
                }
                $uriStr .= '?' . $query->toStr($encode);
            }
        }

        if ($conf['fragment']) {
            $fragment = $this->fragment();
            if (null !== $fragment) {
                if ($shouldReturnOnly('fragment')) {
                    return $encode ? \rawurlencode($fragment) : $fragment;
                }
                $uriStr .= '#' . ($encode ? \rawurlencode($fragment) : $fragment);
            }
        }

        return $uriStr;
    }
}
