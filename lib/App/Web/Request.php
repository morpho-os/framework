<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\Base\IFn;
use Morpho\Base\NotImplementedException;
use function Morpho\Base\trimMore;
use Morpho\App\IResponse;
use Morpho\Fs\Path;
use Morpho\App\Request as BaseRequest;
use Morpho\App\Web\Uri\Authority;
use Morpho\App\Web\Uri\Uri;

/**
 * Some methods in this class based on \Zend\Http\PhpEnvironment\Request class.
 * @see https://github.com/zendframework/zend-http for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license https://github.com/zendframework/zend-http/blob/master/LICENSE.md New BSD License
 */
class Request extends BaseRequest {
    // See https://tools.ietf.org/html/rfc7231#section-4, https://tools.ietf.org/html/rfc5789
    //public const CONNECT_METHOD = 'CONNECT';
    public const DELETE_METHOD = 'DELETE';
    public const GET_METHOD = 'GET';
    //public const HEAD_METHOD = 'HEAD';
    //public const OPTIONS_METHOD = 'OPTIONS';
    public const PATCH_METHOD = 'PATCH';
    public const POST_METHOD = 'POST';
    //public const PUT_METHOD = 'PUT';
    //public const TRACE_METHOD = 'TRACE';

    /**
     * @var \ArrayObject
     */
    protected $headers;

    /**
     * @var ?string
     */
    protected $originalMethod;

    /**
     * @var string|false
     */
    protected $overwrittenMethod;

    /**
     * @var ?bool
     */
    protected $isAjax;

    /**
     * @var ?array
     */
    private $serverVars;

    /**
     * @var ?Uri
     */
    private $uri;

    private static $methods = [
        /*
        self::CONNECT_METHOD,
        */
        self::DELETE_METHOD,
        self::GET_METHOD,
        //self::HEAD_METHOD,
        //self::OPTIONS_METHOD,
        self::POST_METHOD,
        self::PATCH_METHOD,
        //self::PUT_METHOD,
        //self::TRACE_METHOD,
    ];

    /**
     * @var array
     */

    private $trustedProxyIps;

    /**
     * @var IFn
     */
    private $uriChecker;

    public function __construct($params = null, ?array $serverVars = null, IFn $uriChecker = null) {
        parent::__construct(null !== $params ? $params : []);
        $this->serverVars = $serverVars;
        $this->uriChecker = $uriChecker;
        $method = $this->detectOriginalMethod();
        $this->originalMethod = null !== $method ? $method : self::GET_METHOD;
        $this->overwrittenMethod = $this->detectOverwrittenMethod();
    }

    /**
     * Calls one of:
     *     - get()
     *     - patch()
     *     - post()
     * @TODO:
     *     - options()
     *     - delete()
     *     - head()
     *     - put()
     *     - trace()
     *     - connect()
     *     - propfind()
     */
    public function args($names = null, bool $trim = true) {
        $method = $this->method();
        switch ($method) {
            case self::GET_METHOD:
                return $this->query($names, $trim);
            case self::POST_METHOD:
                return $this->post($names, $trim);
            case self::PATCH_METHOD:
                return $this->patch($names, $trim);
            default:
                throw new BadRequestException();
        }
    }

    public function arg($name, bool $trim = true) {
        throw new NotImplementedException();
    }

    public function data(array $source, $name = null, bool $trim = true) {
        // NB: On change sync code with query() and post()
        if (null === $name) {
            return $trim ? trimMore($source) : $source;
        }
        if (\is_array($name)) {
            $data = \array_intersect_key($source, \array_flip(\array_values($name)));
            $data += \array_fill_keys($name, null);
            return $trim ? trimMore($data) : $data;
        }
        if ($trim) {
            return isset($source[$name])
                ? trimMore($source[$name])
                : null;
        }
        return isset($source[$name])
            ? $source[$name]
            : null;
    }

    /**
     * @return mixed @TODO Specify concrete types.
     */
    public function patch($name = null, bool $trim = true) {
        if ($this->overwrittenMethod === self::PATCH_METHOD) {
            return $this->post($name, $trim);
        }
        // @TODO: read from php://input using resource up to 'post_max_size' and 'max_input_vars' php.ini values, check PHP sources for possible handling of the php://input and applying these settings already on PHP core level.
        // @TODO: Replace with MethodNotAllowed extends HttpException, handle MethodNotALlowed in other places.
        throw new HttpException('Method not allowed');
    }

    public function hasPost(string $name): bool {
        return isset($_POST[$name]);
    }

    public function post($name = null, bool $trim = true) {
        // NB: On change sync with data() and query()
        if (null === $name) {
            return $trim ? trimMore($_POST) : $_POST;
        }
        if (\is_array($name)) {
            $data = \array_intersect_key($_POST, \array_flip(\array_values($name)));
            $data += \array_fill_keys($name, null);
            return $trim ? trimMore($data) : $data;
        }
        if ($trim) {
            return isset($_POST[$name])
                ? trimMore($_POST[$name])
                : null;
        }
        return isset($_POST[$name])
            ? $_POST[$name]
            : null;
    }

    public function hasQuery(string $name): bool {
        return isset($_GET[$name]);
    }

    public function query($name = null, bool $trim = true) {
        // NB: On change sync with data() and post()
        if (null === $name) {
            return $trim ? trimMore($_GET) : $_GET;
        }
        if (\is_array($name)) {
            $data = \array_intersect_key($_GET, \array_flip(\array_values($name)));
            $data += \array_fill_keys($name, null);
            return $trim ? trimMore($data) : $data;
        }
        if ($trim) {
            return isset($_GET[$name])
                ? trimMore($_GET[$name])
                : null;
        }
        return isset($_GET[$name])
            ? $_GET[$name]
            : null;
    }

    public function isAjax(bool $flag = null): bool {
        if (null !== $flag) {
            $this->isAjax = (bool)$flag;
        }
        if (null !== $this->isAjax) {
            return $this->isAjax;
        }
        $headers = $this->headers();
        return $headers->offsetExists('X-Requested-With') && $headers->offsetGet('X-Requested-With') === 'XMLHttpRequest';
    }

    public function uri(): Uri {
        if (null === $this->uri) {
            $this->initUri();
        }
        return $this->uri;
    }

    public function setUri(Uri $uri): void {
        $this->uri = $uri;
    }

    /**
     * NB: $method must not be taken from user input.
     * @param string $method
     */
    public function setMethod(string $method): void {
        $this->originalMethod = \strtoupper($method);
        $this->overwrittenMethod = null;
    }

    public function method(): string {
        return null !== $this->overwrittenMethod
            ? $this->overwrittenMethod
            : $this->originalMethod;
    }

/*    public function isConnectMethod(): bool {
        return $this->method() === self::CONNECT_METHOD;
    }*/

    public function isDeleteMethod(): bool {
        return $this->method() === self::DELETE_METHOD;
    }

    public function isGetMethod(): bool {
        return $this->method() === self::GET_METHOD;
    }

/*    public function isHeadMethod(): bool {
        return $this->method() === self::HEAD_METHOD;
    }*/

/*    public function isOptionsMethod(): bool {
        return $this->method() === self::OPTIONS_METHOD;
    }*/

    public function isPatchMethod(): bool {
        return $this->method() === self::PATCH_METHOD;
    }

    public function isPostMethod(): bool {
        return $this->method() === self::POST_METHOD;
    }

/*    public function isPutMethod(): bool {
        return $this->method() === self::PUT_METHOD;
    }

    public function isTraceMethod(): bool {
        return $this->method() === self::TRACE_METHOD;
    }*/

    public function isKnownMethod($method): bool {
        return \is_string($method) && \in_array($method, self::$methods, true);
    }

    public static function knownMethods(): array {
        return self::$methods;
    }

    /**
     * Note: Returned headers can contain user input and therefore can be not safe in some scenarious.
     */
    public function headers(): \ArrayObject {
        if (null === $this->headers) {
            $this->initHeaders();
        }
        return $this->headers;
    }

    public function setTrustedProxyIps(array $ips): void {
        $this->trustedProxyIps = $ips;
    }

    public function trustedProxyIps(): ?array {
        return $this->trustedProxyIps;
    }

    /**
     * @return bool

    public function acceptsJson()
     * {
     * $header = $this->getHeaders()->get('ACCEPT');
     * return false !== $header && false !== stripos($header->getFieldValue(), 'application/json');
     * }
     */

    protected function mkResponse(): IResponse {
        return new Response();
    }

    protected function initHeaders(): void {
        $headers = [];
        foreach ($this->serverVars ?: $_SERVER as $key => $value) {
            if (\strpos($key, 'HTTP_') === 0) {
                if (\strpos($key, 'HTTP_COOKIE') === 0) {
                    // Cookies are handled using the $_COOKIE superglobal
                    continue;
                }
                $name = \strtr(\substr($key, 5), '_', ' ');
                $name = \strtr(\ucwords(\strtolower($name)), ' ', '-');
            } elseif (\strpos($key, 'CONTENT_') === 0) {
                $name = \substr($key, 8); // Content-
                $name = 'Content-' . (($name == 'MD5') ? $name : \ucfirst(\strtolower($name)));
            } else {
                continue;
            }
            $headers[$name] = $value;
        }
        $this->headers = new \ArrayObject($headers);
    }

    /**
     * Based on Request::isSecure() from the https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Request.php
     * (c) Fabien Potencier <fabien@symfony.com>
     */
    protected function isSecure(): bool {
        $https = $this->serverVar('HTTPS');
        if ($https) {
            return 'off' !== \strtolower($https);
        }
        if ($this->isFromTrustedProxy()) {
            return \in_array(\strtolower($this->serverVar('HTTP_X_FORWARDED_PROTO', '')), ['https', 'on', 'ssl', '1'], true);
        }
        return false;
    }

    protected function initUri(): void {
        $uri = new Uri();

        $uri->setScheme($this->isSecure() ? 'https' : 'http');

        $authority = new Authority();
        [$host, $port] = $this->detectHostAndPort();
        if ($host) {
            $authority->setHost($host);
            if ($port) {
                $authority->setPort($port);
            }
        }
        $uri->setAuthority($authority);

        $requestUri = $this->detectRequestUri();
        $uri->setPath($requestUri);

        $uri->path()->setBasePath($this->detectBasePath($requestUri));

        $queryStr = $this->serverVar('QUERY_STRING');
        if ($queryStr !== '') {
            $uri->setQuery($queryStr);
        }

        $this->checkUri($uri);

        $this->uri = $uri;
    }

    protected function detectHostAndPort(): array {
        // URI host & port
        $host = null;
        $port = null;

        // Set the host
        if ($this->headers()->offsetExists('Host')) {
            $host = $this->headers()->offsetGet('Host');

            // works for regname, IPv4 & IPv6
            if (\preg_match('~\:(\d+)$~', $host, $matches)) {
                $host = \substr($host, 0, -1 * (\strlen($matches[1]) + 1));
                $port = (int)$matches[1];
            }

/*            // set up a validator that check if the hostname is legal (not spoofed)
            $hostnameValidator = new HostnameValidator([
                'allow'       => HostnameValidator::ALLOW_ALL,
                'useIdnCheck' => false,
                'useTldCheck' => false,
            ]);
            // If invalid. Reset the host & port
            if (!$hostnameValidator->isValid($host)) {
                $host = null;
                $port = null;
            }*/
        }

        $serverName = $this->serverVar('SERVER_NAME');
        if (!$host && $serverName) {
            $host = $serverName;
            $port = \intval($this->serverVar('SERVER_PORT', -1));
            if ($port < 1) {
                $port = null;
            } else {
                // Check for missinterpreted IPv6-Address
                // Reported at least for Safari on Windows
                $serverAddr = $this->serverVar('SERVER_ADDR');
                if (isset($serverAddr) && \preg_match('/^\[[0-9a-fA-F\:]+\]$/', $host)) {
                    $host = '[' . $serverAddr . ']';
                    if ($port . ']' == \substr($host, \strrpos($host, ':') + 1)) {
                        // The last digit of the IPv6-Address has been taken as port
                        // Unset the port so the default port can be used
                        $port = null;
                    }
                }
            }
        }
        return [$host, $port];
    }

    protected function detectRequestUri(): string {
        $requestUri = $this->serverVar('REQUEST_URI');

        $normalizeUri = function ($requestUri) {
            if (($qpos = \strpos($requestUri, '?')) !== false) {
                return \substr($requestUri, 0, $qpos);
            }
            return $requestUri;
        };

        // Check this first so IIS will catch.
        $httpXRewriteUrl = $this->serverVar('HTTP_X_REWRITE_URL');
        if ($httpXRewriteUrl !== null) {
            $requestUri = $httpXRewriteUrl;
        }

        // Check for IIS 7.0 or later with ISAPI_Rewrite
        $httpXOriginalUrl = $this->serverVar('HTTP_X_ORIGINAL_URL');
        if ($httpXOriginalUrl !== null) {
            $requestUri = $httpXOriginalUrl;
        }

        // IIS7 with URL Rewrite: make sure we get the unencoded url
        // (double slash problem).
        $iisUrlRewritten = $this->serverVar('IIS_WasUrlRewritten');
        $unencodedUrl    = $this->serverVar('UNENCODED_URL', '');
        if ('1' == $iisUrlRewritten && '' !== $unencodedUrl) {
            return $normalizeUri($unencodedUrl);
        }

        if ($requestUri !== null) {
            return $normalizeUri(\preg_replace('#^[^/:]+://[^/]+#', '', $requestUri));
        }

        // IIS 5.0, PHP as CGI.
        $origPathInfo = $this->serverVar('ORIG_PATH_INFO');
        if ($origPathInfo !== null) {
            $queryString = $this->serverVar('QUERY_STRING', '');
            if ($queryString !== '') {
                $origPathInfo .= '?' . $queryString;
            }
            return $normalizeUri($origPathInfo);
        }

        return '/';
    }

    protected function detectBasePath(string $requestUri): string {
        $scriptName = $this->serverVar('SCRIPT_NAME', '');
        if ('' === $scriptName) {
            return '/';
        }
        $basePath = \ltrim(Path::normalize(\dirname($scriptName)), '/');
/*        if (!Uri::validatePath($basePath)) {
            throw new BadRequestException();
        }*/
        return '/' . $basePath;
    }

    protected function isFromTrustedProxy(): bool {
        return null !== $this->trustedProxyIps && \in_array($this->serverVar('REMOTE_ADDR'), $this->trustedProxyIps, true);
    }

    /**
     * @return mixed
     */
    protected function serverVar(string $name, $default = null) {
        if (null !== $this->serverVars) {
            return $this->serverVars[$name] ?? $default;
        }
        return $_SERVER[$name] ?? $default;
    }

    /**
     * Must throw the BadRequestException if the $uri can be considered as dangerous.
     */
    protected function checkUri(Uri $uri): void {
        $checked = $this->uriChecker->__invoke($uri);
        if (!$checked) {
            throw new BadRequestException('Invalid URI'); // or AccessDenied?
        }
    }

    protected function detectOriginalMethod(): ?string {
        $httpMethod = $this->serverVar('REQUEST_METHOD');
        if (null !== $httpMethod) {
            $httpMethod = \strtoupper((string)$httpMethod);
            if ($this->isKnownMethod($httpMethod)) {
                return $httpMethod;
            }
        }
        return null;
    }

    protected function detectOverwrittenMethod(): ?string {
        $overwrittenMethod = null;
        $httpMethod = $this->serverVar('HTTP_X_HTTP_METHOD_OVERRIDE');
        if (null !== $httpMethod) {
            $overwrittenMethod = (string) $httpMethod;
        } elseif (isset($_GET['_method'])) {
            // Allow to pass a method through the special '_method' item.
            $overwrittenMethod = (string)$_GET['_method'];
            unset($_GET['_method']);
        } elseif (isset($_POST['_method'])) {
            $overwrittenMethod = (string)$_POST['_method'];
            unset($_POST['_method']);
        }
        if (null !== $overwrittenMethod) {
            $overwrittenMethod = \strtoupper($overwrittenMethod);
            if ($this->isKnownMethod($overwrittenMethod)) {
                return $overwrittenMethod;
            }
        }
        return null;
    }
}
