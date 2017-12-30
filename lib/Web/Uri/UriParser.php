<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\Uri;

use Morpho\Base\IFn;

/**
 * [RFC 3986](https://tools.ietf.org/html/rfc3986) compatible URI parser.
 */
class UriParser implements IFn {
    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var array
     */
    protected $semiParsed;

    public function __invoke($uri): Uri {
        $this->uri = new Uri();

        # We use modified [regular expression from the RFC 3986](https://tools.ietf.org/html/rfc3986#appendix-B)
        if (!preg_match('~^
            ((?P<scheme>[^:/?\#]+):)?                      # scheme
            (?P<authority_>//(?P<authority>[^/?\#]*))?     # authority
            (?P<path>[^?\#]*)                              # path
            (?P<query_>\?(?P<query>[^\#]*))?               # query
            (?P<fragment_>\#(?P<fragment>.*))?             # fragment
            $~six', $uri, $match)) {
            throw new UriParseException('Invalid URI');
        }
        $this->semiParsed = $match;

        $this->parseScheme();
        $this->parseAuthority();
        $this->parsePath();
        $this->parseQuery();
        $this->parseFragment();

        return $this->uri;
    }

    public static function parseOnlyAuthority(string $authorityStr): Authority {
        // authority = [ userinfo "@" ] host [ ":" port ]
        $authority = new Authority();
        if ($authorityStr === '') {
            $authority->setUserInfo('');
            $authority->setHost('');
            return $authority;
        }
        if (!preg_match('~^
            (?P<userInfo_>(?P<userInfo>[^@]*)@)?
            (?P<host>(?:\[[^\]]+\]|[^:]+)?)
            (:(?P<port>\d+))?
            $~six', $authorityStr, $authorityMatch)) {
            return $authority;
        }
        $hasUserInfo = $authorityMatch['userInfo_'] !== '';
        $authority->setUserInfo($hasUserInfo ? $authorityMatch['userInfo'] : null);
        $authority->setHost($authorityMatch['host']);
        $authority->setPort(isset($authorityMatch['port']) ? (int)$authorityMatch['port'] : null);
        return $authority;
    }

    public static function parseOnlyQuery(string $query): Query {
        if ($query === '') {
            return new Query();
        }
        // NB: The parse_str() for the 'foo' string returns ['foo' => ''], but we need to return ['foo' => null], so we can't use it
        $parts = explode('&', $query);
        $queryArgs = [];
        $setValue = function ($key, $value) use (&$queryArgs) {
            $stack = [];
            $state = null;
            $k = null;
            for ($i = 0, $n = mb_strlen($key); $i < $n; $i++) {
                $ch = mb_substr($key, $i, 1);
                switch ($state) {
                    case null:
                        // expect the arg name.
                        if ($ch === '[') {
                            if ($k === null) {
                                return false;
                            }
                            $state = '[';
                            $stack[] = $k;
                            $k = null;
                        } elseif ($ch === ']') {
                            return false;
                        } else {
                            $k .= $ch;
                        }
                        break;
                    case '[':
                        // expect the ']' or arg name
                        if ($ch === ']') {
                            $state = ']';
                            $stack[] = $k;
                            $k = null;
                        } elseif ($ch === '[') {
                            return false;
                        } else {
                            $k .= $ch;
                        }
                        break;
                    case ']':
                        // expect the '['
                        if ($ch !== '[') {
                            return false;
                        } else {
                            $state = '[';
                        }
                        break;
                    default:
                        throw new \LogicException();
                }
            }
            if ($state !== ']') {
                return false;
            }

            $q = &$queryArgs;
            foreach ($stack as $key) {
                if (null === $key) { // null means [], e.g. arr[]
                    $q[] = null;
                    $key = count($q) - 1;
                    $q = &$q[$key];
                } else {
                    $q = &$q[$key];
                }
            }
            $q = $value;
            unset($q);
        };
        foreach ($parts as $part) {
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) == 1) {
                $key = $keyValue[0];
                $value = null;
            } else {
                $key = $keyValue[0];
                $value = $keyValue[1];
            }
            if (!$key) {
                continue;
            }
            if (false !== strpos($key, '[') || false !== strpos($key, ']')) {
                $res = $setValue($key, $value);
                if (false === $res) {
                    continue;
                }
            } else {
                $queryArgs[$key] = $value;
            }
        }
        return new Query($queryArgs);
    }
    
    protected function parseScheme(): void {
        $scheme = $this->semiParsed['scheme'];
        $this->uri->setScheme($scheme);
    }

    protected function parseAuthority(): void {
        $hasAuthority = $this->semiParsed['authority_'] !== '';
        if ($hasAuthority) {
            $authority = $this->semiParsed['authority'];
            $this->uri->setAuthority($authority);
        }
    }

    protected function parsePath(): void {
        $path = $this->semiParsed['path'];
        $this->uri->setPath($path);
    }

    protected function parseQuery(): void {
        $hasQuery = isset($this->semiParsed['query_']) && $this->semiParsed['query_'] !== '';
        if ($hasQuery) {
            $this->uri->setQuery($this->semiParsed['query']);
        }
    }

    protected function parseFragment(): void {
        $hasFragment = isset($this->semiParsed['fragment_']) && $this->semiParsed['fragment_'] !== '';
        if ($hasFragment) {
            $this->uri->setFragment($this->semiParsed['fragment']);
        }
    }
}