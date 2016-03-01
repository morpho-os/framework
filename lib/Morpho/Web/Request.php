<?php
//declare(strict_types = 1);
namespace Morpho\Web;

use Morpho\Base\NotImplementedException;
use function Morpho\Base\trimMore;
use Morpho\Core\Request as BaseRequest;
use Zend\Validator\Hostname as HostNameValidator;
use Zend\Http\Headers;

/**
 * Some chunks of code for this class was taken from the Request class
 * from Zend Framework 2.x (http://framework.zend.com/),
 * @TODO: Specify what chunks and mark of them specially.
 */
class Request extends BaseRequest {
    const OPTIONS_METHOD = 'OPTIONS';
    const GET_METHOD = 'GET';
    const HEAD_METHOD = 'HEAD';
    const POST_METHOD = 'POST';
    const PUT_METHOD = 'PUT';
    const DELETE_METHOD = 'DELETE';
    const TRACE_METHOD = 'TRACE';
    const CONNECT_METHOD = 'CONNECT';
    const PATCH_METHOD = 'PATCH';
    const PROPFIND_METHOD = 'PROPFIND';

    protected $headers;

    protected $method;

    private $uri;

    private $mapPostTo;

    private static $allMethods = [
        self::OPTIONS_METHOD,
        self::GET_METHOD,
        self::HEAD_METHOD,
        self::POST_METHOD,
        self::PUT_METHOD,
        self::DELETE_METHOD,
        self::TRACE_METHOD,
        self::CONNECT_METHOD,
        self::PATCH_METHOD,
        self::PROPFIND_METHOD,
    ];

    public function getContent(): string {
        // @TODO
        return file_get_contents('php://input');
    }

    public function getParsedContent(): array {
        return Uri::stringToQueryArgs($this->getContent());
    }

    public function getArgs($name = null, bool $trim = true) {
        return $this->{'get' . $this->getMethod()}($name, $trim);
    }

    public function data(array $source, $name, bool $trim = true) {
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

    public function getPatch($name = null, bool $trim = true) {
        return $this->data(
            $this->mapPostTo === self::PATCH_METHOD ? $_POST : $this->getParsedContent(),
            $name,
            $trim
        );
    }

    public function getPut($name = null, bool $trim = true) {
        return $this->data(
            $this->mapPostTo === self::PUT_METHOD ? $_POST : $this->getParsedContent(),
            $name,
            $trim
        );
    }

    public function getDelete($name = null, bool $trim = true) {
        throw new NotImplementedException();
    }

    public function hasPost(string $name) {
        return isset($_POST[$name]);
    }

    public function getPost($name = null, bool $trim = true) {
        // @TODO: Optimize this method for memory for usage.
        return $this->data($_POST, $name, $trim);
    }

    public function hasGet(string $name) {
        return isset($_GET[$name]);
    }

    public function getGet($name = null, bool $trim = true) {
        // @TODO: Optimize this method for memory usage.
        return $this->data($_GET, $name, $trim);
    }

    public function isAjax(): bool {
        $header = $this->getHeaders()->get('X_REQUESTED_WITH');
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

    public function setUri(Uri $uri) {
        $this->uri = $uri;
    }

    public function getUriPath(): string {
        if (null === $this->uri) {
            $this->initUri();
        }
        return $this->uri->getPath();
    }

    public function setMethod(string $method): self {
        $this->method = $this->normalizeMethod($method);
        return $this;
    }

    public function getMethod(): string {
        if (null === $this->method) {
            // Handle the '_method' like in 'Ruby on Rails'.
            if (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
                if (in_array($method, [self::PATCH_METHOD, self::DELETE_METHOD, self::PUT_METHOD], true)) {
                    $this->method = $method;
                    $this->mapPostTo = $method;
                    return $method;
                }
            }
            $this->method = $this->normalizeMethod($_SERVER['REQUEST_METHOD']);
        }
        return $this->method;
    }

    public function isOptionsMethod(): bool {
        return $this->getMethod() === self::OPTIONS_METHOD;
    }

    public function isGetMethod(): bool {
        return $this->getMethod() === self::GET_METHOD;
    }

    public function isHeadMethod(): bool {
        return $this->getMethod() === self::HEAD_METHOD;
    }

    public function isPostMethod(): bool {
        return $this->getMethod() === self::POST_METHOD;
    }

    public function isPutMethod(): bool {
        return $this->getMethod() === self::PUT_METHOD;
    }

    public function isDeleteMethod(): bool {
        return $this->getMethod() === self::DELETE_METHOD;
    }

    public function isPatchMethod(): bool {
        return $this->getMethod() === self::PATCH_METHOD;
    }

    public function isTraceMethod(): bool {
        return $this->getMethod() === self::TRACE_METHOD;
    }

    public function isConnectMethod(): bool {
        return $this->getMethod() === self::CONNECT_METHOD;
    }

    public function isPropfindMethod(): bool {
        return $this->getMethod() === self::PROPFIND_METHOD;
    }

    public static function isValidMethod(string $httpMethod): bool {
        return in_array($httpMethod, self::getAllMethods(), true);
    }

    public static function normalizeMethod(string $httpMethod): string {
        $method = strtoupper($httpMethod);
        if (!self::isValidMethod($method)) {
            $method = self::GET_METHOD;
        }
        return $method;
    }

    public static function getAllMethods(): array {
        return self::$allMethods;
    }

    public function getHeader($name, $default = false) {
        $headers = $this->getHeaders();
        return $headers->has($name)
            ? $headers->get($name)
            : $default;
    }

    public function getHeaders() {
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

    protected function createResponse(): Response {
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
        if ($this->getHeaders()->get('host')) {
            $host = $this->getHeaders()->get('host')->getFieldValue();

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
        $basePath = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if (!Uri::validatePath($basePath)) {
            throw new BadRequestException();
        }
        return '/' . $basePath;
    }
}
