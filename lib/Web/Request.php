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
use Zend\Validator\Hostname as HostNameValidator;
use Zend\Http\Headers;
use Morpho\Core\Request as BaseRequest;

/**
 * Some chunks of code for this class was taken from the Request class
 * from Zend Framework 2.x (http://framework.zend.com/),
 * @TODO: Specify what chunks and mark of them specially.
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

    protected $headers;

    protected $method;
    
    protected $isAjax;

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

    protected $routingParams = [];

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

    public function hasQuery(string $name) {
        return isset($_GET[$name]);
    }

    public function query($name = null, bool $trim = true) {
        // @TODO: Optimize this method for memory usage.
        return $this->data($_GET, $name, $trim);
    }

    public function isAjax($flag = null): bool {
        if (null !== $flag) {
            $this->isAjax = (bool)$flag;
        }
        if (null !== $this->isAjax) {
            return $this->isAjax;
        }
        $header = $this->headers()->get('X_REQUESTED_WITH');
        return false !== $header && $header->getFieldValue() == 'XMLHttpRequest';
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

    public function header($name, $default = false) {
        $headers = $this->headers();
        return $headers->has($name)
            ? $headers->get($name)
            : $default;
    }

    public function headers() {
        if (null === $this->headers) {
            $this->initHeaders();
        }
        return $this->headers;
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

    protected function initHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if ($value && strpos($key, 'HTTP_') === 0) {
                if (strpos($key, 'HTTP_COOKIE') === 0) {
                    // Cookies are handled using the $_COOKIE superglobal
                    continue;
                }
                $name = strtr(substr($key, 5), '_', ' ');
                $name = strtr(ucwords(strtolower($name)), ' ', '-');
            } elseif ($value && strpos($key, 'CONTENT_') === 0) {
                $name = substr($key, 8); // Content-
                $name = 'Content-' . (($name == 'MD5') ? $name : ucfirst(strtolower($name)));
            } else {
                continue;
            }
            $headers[$name] = $value;
        }
        $this->headers = $hdrs = new Headers();
        $hdrs->addHeaders($headers);
    }

    /**
     * This method uses chunks of code found in the \Zend\Http\PhpEnvironment\Request::setServer() method.
     */
    protected function initUri() {
        $uri = new Uri();

        if ((!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        ) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }
        $uri->setScheme($scheme);

        // URI host & port
        $host = null;
        $port = null;

        // Set the host
        if ($this->headers()->get('host')) {
            $host = $this->headers()->get('host')->getFieldValue();

            // works for regname, IPv4 & IPv6
            if (preg_match('|\:(\d+)$|', $host, $matches)) {
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

        if (!$host && isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
            if (isset($_SERVER['SERVER_PORT'])) {
                $port = (int)$_SERVER['SERVER_PORT'];
            }
            // Check for missinterpreted IPv6-Address
            // Reported at least for Safari on Windows
            if (isset($_SERVER['SERVER_ADDR']) && preg_match('/^\[[0-9a-fA-F\:]+\]$/', $host)) {
                $host = '[' . $_SERVER['SERVER_ADDR'] . ']';
                if ($port . ']' == substr($host, strrpos($host, ':') + 1)) {
                    // The last digit of the IPv6-Address has been taken as port
                    // Unset the port so the default port can be used
                    $port = null;
                }
            }
        }
        $uri->setHost($host);
        $uri->setPort($port);

        // URI path
        $requestUri = $_SERVER['REQUEST_URI'];
        if (($qpos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $qpos);
        }

        $uri->setPath($requestUri);

        // URI query
        if (isset($_SERVER['QUERY_STRING'])) {
            $uri->setQuery($_SERVER['QUERY_STRING']);
        }

        $uri->setBasePath($this->detectBasePath());

        $this->uri = $uri;
    }

    protected function detectBasePath(): string {
        // @TODO: Check on Windows.
        $basePath = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if (!Uri::validatePath($basePath)) {
            throw new BadRequestException();
        }
        return '/' . $basePath;
    }

    private static function normalizedMethod($httpMethod): string {
        if (!$httpMethod) {
            if (!isset($_SERVER['REQUEST_METHOD'])) {
                return self::GET_METHOD;
            }
            $httpMethod = $_SERVER['REQUEST_METHOD'];
        }
        $method = strtoupper($httpMethod);
        return self::isValidMethod($method) ? $method : self::GET_METHOD;
    }
}
