<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Fs\Path;
use Zend\Uri\Http as BaseUri;

class Uri extends BaseUri {
    const BASE64_URI_REGEXP = '[A-Za-z0-9+\\-_]';

    private $basePath;
    const QUERY_PART_SEPARATOR = '?';
    const QUERY_ARG_SEPARATOR = '&';
    const FRAGMENT_PART_SEPARATOR = '#';

    public static function hasAuthority(string $uri): bool {
        return strlen($uri) > 2 && false !== strpos($uri, '//');
    }

    public function path() {
        return $this->path;
    }

    public function isPathEqualTo(string $path): bool {
        return $this->path() === $path;
    }

    public function setBasePath(string $basePath): self {
        $this->basePath = $basePath;
        return $this;
    }

    public function basePath(): string {
        return $this->basePath;
    }

    public function setQuery($query): self {
        $this->query = is_array($query) ? self::queryArgsToString($query) : $query;
        return $this;
    }

    public function query() {
        return $this->query;
    }

    public function unsetQueryArg($name): self {
        $query = $this->query();
        if (null !== $query) {
            $queryArgs = self::stringToQueryArgs($query);
            unset($queryArgs[$name]);
            $this->setQuery($queryArgs);
        }
        return $this;
    }

    public static function stringToQueryArgs(string $query): array {
        $queryArgs = [];
        parse_str($query, $queryArgs);
        return $queryArgs;
    }

    public static function queryArgsToString(array $queryArgs): string {
        return str_replace('+', '%20', http_build_query($queryArgs));
    }

    public function appendQueryArgs(array $queryArgs): self {
        $query = $this->query();
        $this->setQuery(
            $query
            . (!empty($query)
                ? self::QUERY_ARG_SEPARATOR . self::queryArgsToString($queryArgs)
                : self::queryArgsToString($queryArgs)
            )
        );
        return $this;
    }

    /**
     * Returns $path + $queryPart + $fragmentPart.
     */
    public function relativeRef(): string {
        return $this->path()
            . $this->queryPart()
            . $this->fragmentPart();
    }

    /**
     * Returns the '?' + $query if $query is not empty, returns empty string otherwise.
     */
    public function queryPart(): string {
        $query = $this->query();
        return !empty($query) ? self::QUERY_PART_SEPARATOR . $query : '';
    }

    public function fragment() {
        return $this->fragment;
    }

    /**
     * Returns the '?' + $fragment if $fragment is not empty, returns empty string otherwise.
     */
    public function fragmentPart(): string {
        $fragment = $this->fragment;
        return !empty($fragment) ? self::FRAGMENT_PART_SEPARATOR . $fragment : '';
    }

    /**
     * Returns $uri as is if it contains authority, returns the $uri prepended with $basePath otherwise.
     */
    public function prependBasePath(string $uri): string {
        if (Uri::hasAuthority($uri)) {
            return $uri;
        }
        $basePath = $this->basePath();
        if (strlen($uri) && ($uri[0] === self::QUERY_PART_SEPARATOR || $uri[0] === self::FRAGMENT_PART_SEPARATOR)) {
            return $basePath . $uri;
        }
        return Path::combine($basePath, $uri);
    }

    public static function encode(string $uri): string {
        return str_replace('%2F', '/', rawurlencode($uri));
    }

    /**
     * @see http://tools.ietf.org/html/rfc4648#section-5
     * @see http://php.net/base64_encode#103849
     */
    public static function base64Encode(string $uri): string {
        return rtrim(
            strtr(
                base64_encode($uri),
                '+/',
                '-_'
            ),
            '='
        );
    }

    public static function base64Decode(string $uri): string {
        return base64_decode(
            str_pad(
                strtr($uri, '-_', '+/'),
                strlen($uri) % 4,
                '=',
                STR_PAD_RIGHT
            )
        );
    }

    public function scheme() {
        return $this->getScheme();
    }
}