<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
//declare(strict_types = 1);
namespace Morpho\Web;

use function Morpho\Base\trimMore;
use Morpho\Core\IResponse;
use Morpho\Fs\Path;
use Zend\Validator\Hostname as HostNameValidator;
use Morpho\Core\Request as BaseRequest;

/**
 * This class based on \Zend\Http\PhpEnvironment\Request class.
 * @see https://github.com/zendframework/zend-http for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license https://github.com/zendframework/zend-http/blob/master/LICENSE.md New BSD License
 */
class Request extends BaseRequest {
    // See https://tools.ietf.org/html/rfc7231#section-4
    public const CONNECT_METHOD = 'CONNECT';
    public const DELETE_METHOD = 'DELETE';
    public const GET_METHOD = 'GET';
    public const HEAD_METHOD = 'HEAD';
    public const OPTIONS_METHOD = 'OPTIONS';
    public const POST_METHOD = 'POST';
    public const PUT_METHOD = 'PUT';
    public const TRACE_METHOD = 'TRACE';

    /**
     * @var \ArrayObject
     */
    protected $headers;

    /**
     * @var ?string
     */
    protected $method;

    /**
     * @var ?bool
     */
    protected $isAjax;

    /**
     * @var array
     */
    protected $routingParams = [];

    /**
     * @var ?array
     */
    private $serverVars;

    /**
     * @var ?Uri
     */
    private $uri;

    private $mapPostTo;

    private static $methods = [
        self::CONNECT_METHOD,
        self::DELETE_METHOD,
        self::GET_METHOD,
        self::HEAD_METHOD,
        self::OPTIONS_METHOD,
        self::POST_METHOD,
        self::PUT_METHOD,
        self::TRACE_METHOD,
    ];

    /**
     * @var array
     */
    private $trustedProxyIps;

    public function __construct(array $serverVars = null) {
        $this->serverVars = $serverVars;
    }

    public function hasRoutingParams(): bool {
        return count($this->routingParams) > 0;
    }

    public function setRoutingParams(array $params): void {
        $this->routingParams = $params;
    }

    public function routingParams(): array {
        return $this->routingParams;
    }

    public function setRoutingParam(string $name, $value): void {
        $this->routingParams[$name] = $value;
    }

    public function routingParam(string $name, $default = null) {
        return isset($this->routingParams[$name]) ? $this->routingParams[$name] : $default;
    }

    public function content(): string {
        // @TODO
        return file_get_contents('php://input');
    }

    public function parsedContent(): array {
        // @TODO: Ensure that it is safe.
        return Uri::stringToQueryArgs($this->content());
    }

    /**
     * Calls one of:
     *     - get()
     *     - post()
     *     - put()
     * @TODO:
     *     - options()
     *     - head()
     *     - delete(),
     *     - patch()
     *     - trace()
     *     - connect()
     *     - propfind()
     */
    public function args($name = null, bool $trim = true) {
        $method = $this->method();
        switch ($method) {
            case self::GET_METHOD:
                return $this->query($name, $trim);
            case self::POST_METHOD:
            //case self::PATCH_METHOD:
            case self::PUT_METHOD:
                return $this->$method($name, $trim);
            default:
                new BadRequestException();
        }
    }

    public function data(array $source, $name = null, bool $trim = true) {
        // @TODO: Optimize this method for memory usage.
        if (null === $name) {
            return $trim ? trimMore($source) : $source;
        }
        if (is_array($name)) {
            $data = array_intersect_key($source, array_flip(array_values($name)));
            $data += array_fill_keys($name, null);
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

/*    public function patch($name = null, bool $trim = true) {
        return $this->data(
            $this->mapPostTo === self::PATCH_METHOD ? $_POST : $this->parsedContent(),
            $name,
            $trim
        );
    }*/

    public function put($name = null, bool $trim = true) {
        return $this->data(
            $this->mapPostTo === self::PUT_METHOD ? $_POST : $this->parsedContent(),
            $name,
            $trim
        );
    }

    public function hasPost(string $name) {
        return isset($_POST[$name]);
    }

    public function post($name = null, bool $trim = true) {
        // @TODO: Optimize this method for memory for usage.
        return $this->data($_POST, $name, $trim);
    }

    public function hasQuery(string $name): bool {
        return isset($_GET[$name]);
    }

    public function query($name = null, bool $trim = true) {
        // @TODO: Optimize this method for memory usage.
        return $this->data($_GET, $name, $trim);
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

    /**
     * Returns URI that can be changed without changing original URI (clone).
     *
     * @param string|array|null $queryArgs
     */
    public function uri(): Uri {
        if (null === $this->uri) {
            $this->initUri();
        }
        return $this->uri;
    }

    public function setUri(Uri $uri): self {
        $this->uri = $uri;
        return $this;
    }

    public function uriPath(): string {
        if (null === $this->uri) {
            $this->initUri();
        }
        return $this->uri->path();
    }

    public function setMethod(string $method): self {
        $this->method = $this->normalizedMethod($method);
        return $this;
    }

    public function method(): string {
        if (null === $this->method) {
            // Handle the '_method' like in 'Ruby on Rails'.
            if (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
                if (in_array($method, [/*self::PATCH_METHOD, */self::DELETE_METHOD, self::PUT_METHOD], true)) {
                    $this->method = $method;
                    $this->mapPostTo = $method;
                    return $method;
                }
            }
            $this->method = $this->normalizedMethod(null);
        }
        return $this->method;
    }

    public function isConnectMethod(): bool {
        return $this->method() === self::CONNECT_METHOD;
    }

    public function isDeleteMethod(): bool {
        return $this->method() === self::DELETE_METHOD;
    }

    public function isGetMethod(): bool {
        return $this->method() === self::GET_METHOD;
    }

    public function isHeadMethod(): bool {
        return $this->method() === self::HEAD_METHOD;
    }

    public function isOptionsMethod(): bool {
        return $this->method() === self::OPTIONS_METHOD;
    }

    public function isPostMethod(): bool {
        return $this->method() === self::POST_METHOD;
    }

    public function isPutMethod(): bool {
        return $this->method() === self::PUT_METHOD;
    }

    public function isTraceMethod(): bool {
        return $this->method() === self::TRACE_METHOD;
    }

    public static function isValidMethod(string $httpMethod): bool {
        return in_array($httpMethod, self::$methods, true);
    }

    public static function methods(): array {
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

    protected function newResponse(): IResponse {
        return new Response();
    }

    protected function initHeaders(): void{
        $headers = [];
        foreach ($this->serverVars ?: $_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                if (strpos($key, 'HTTP_COOKIE') === 0) {
                    // Cookies are handled using the $_COOKIE superglobal
                    continue;
                }
                $name = strtr(substr($key, 5), '_', ' ');
                $name = strtr(ucwords(strtolower($name)), ' ', '-');
            } elseif (strpos($key, 'CONTENT_') === 0) {
                $name = substr($key, 8); // Content-
                $name = 'Content-' . (($name == 'MD5') ? $name : ucfirst(strtolower($name)));
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
            return 'off' !== strtolower($https);
        }
        if ($this->isFromTrustedProxy()) {
            return in_array(strtolower($this->serverVar('HTTP_X_FORWARDED_PROTO', '')), ['https', 'on', 'ssl', '1'], true);
        }
        return false;
    }

    protected function initUri(): void {
        $uri = new Uri();

        $uri->setScheme($this->isSecure() ? 'https' : 'http');

        [$host, $port] = $this->detectHostAndPort();
        if ($host) {
            $uri->setHost($host);
            if ($port) {
                $uri->setPort($port);
            }
        }

        $requestUri = $this->detectRequestUri();

        $uri->setPath($requestUri);

        $query = $this->serverVar('QUERY_STRING');
        if (null !== $query) {
            $uri->setQuery($query);
        }

        $uri->setBasePath($this->detectBasePath($requestUri));

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
            if (preg_match('~\:(\d+)$~', $host, $matches)) {
                $host = substr($host, 0, -1 * (strlen($matches[1]) + 1));
                $port = (int)$matches[1];
            }

            // set up a validator that check if the hostname is legal (not spoofed)
            $hostnameValidator = new HostnameValidator([
                'allow'       => HostnameValidator::ALLOW_ALL,
                'useIdnCheck' => false,
                'useTldCheck' => false,
            ]);
            // If invalid. Reset the host & port
            if (!$hostnameValidator->isValid($host)) {
                $host = null;
                $port = null;
            }
        }

        $serverName = $this->serverVar('SERVER_NAME');
        if (!$host && $serverName) {
            $host = $serverName;
            $port = intval($this->serverVar('SERVER_PORT', -1));
            if ($port < 1) {
                $port = null;
            } else {
                // Check for missinterpreted IPv6-Address
                // Reported at least for Safari on Windows
                $serverAddr = $this->serverVar('SERVER_ADDR');
                if (isset($serverAddr) && preg_match('/^\[[0-9a-fA-F\:]+\]$/', $host)) {
                    $host = '[' . $serverAddr . ']';
                    if ($port . ']' == substr($host, strrpos($host, ':') + 1)) {
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
            if (($qpos = strpos($requestUri, '?')) !== false) {
                return substr($requestUri, 0, $qpos);
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
            return $normalizeUri(preg_replace('#^[^/:]+://[^/]+#', '', $requestUri));
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
        $basePath = ltrim(Path::normalize(dirname($this->serverVar('SCRIPT_NAME'))), '/');
        if (!Uri::validatePath($basePath)) {
            throw new BadRequestException();
        }
        return '/' . $basePath;
    }

    protected function isFromTrustedProxy(): bool {
        return null !== $this->trustedProxyIps && in_array($this->serverVar('REMOTE_ADDR'), $this->trustedProxyIps, true);
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

    private function normalizedMethod(?string $httpMethod): string {
        if (!$httpMethod) {
            $requestMethod = $this->serverVar('REQUEST_METHOD');
            if (null === $requestMethod) {
                return self::GET_METHOD;
            }
            $httpMethod = $requestMethod;
        }
        $method = strtoupper($httpMethod);
        return self::isValidMethod($method) ? $method : self::GET_METHOD;
    }
}
