<?php
declare(strict_types=1);

namespace Morpho\Web;

use Morpho\Base\NotImplementedException;
use Morpho\Core\Request as BaseRequest;
use Morpho\Fs\Path;
use Zend\Http\Headers;
use Zend\Uri\Http as HttpUri;

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

    protected $uri;

    protected $headers;

    protected $baseRelUri;

    protected $method;

    protected $content;

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
        if (null === $this->content) {
            $this->content = file_get_contents('php://input');
        }
        return $this->content;
    }

    public function getData($name = null, bool $trim = true) {
        return $this->{'get' . $this->getMethod()}($name, $trim);
    }

    public function getPost($name = null, bool $trim = true) {
        if (null === $name) {
            return $trim ? trimMore($_POST) : $_POST;
        }
        if (is_array($name)) {
            $data = array_intersect_key($_POST, array_flip(array_values($name)));
            $data += array_fill_keys($name, null);
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

    public function getGet($name = null, bool $trim = true) {
        if (null === $name) {
            return $trim ? trimMore($_GET) : $_GET;
        }
        if (is_array($name)) {
            $data = array_intersect_key($_GET, array_flip(array_values($name)));
            $data += array_fill_keys($name, null);
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

    public function isAjax(): bool {
        $header = $this->getHeaders()->get('X_REQUESTED_WITH');
        return false !== $header && $header->getFieldValue() == 'XMLHttpRequest';
    }

    public function getBaseRelativeUri(): string {
        if (null === $this->baseRelUri) {
            $this->baseRelUri = '/' . trim(dirname($_SERVER['SCRIPT_NAME']), '/');
        }
        return $this->baseRelUri;
    }

    public function getRelativeUri($relUri = null, array $params = null, array $args = null) {
        if (null !== $params) {
            throw new NotImplementedException();
        }

        $paths = [];
        if (null === $relUri) {
            $paths[] = $this->getUri()->getPath();
        } else {
            $paths[] = $this->getBaseRelativeUri();
            $paths[] = $relUri;
        }

        $newRelUri = Path::combine($paths);

        if (null !== $args) {
            $uriClone = clone $this->getUri();
            $uriClone->setQuery($args);
            $uriClone->setPath($newRelUri);
            return $uriClone->__toString();
        }

        return $newRelUri;
    }

    public function setUri($uri) {
        $this->uri = is_string($uri) ? new HttpUri($uri) : $uri;
    }

    public function getUri(): HttpUri {
        if (null === $this->uri) {
            $this->uri = new HttpUri($_SERVER['REQUEST_URI']);
        }
        return $this->uri;
    }

    public function setMethod(string $method): self {
        $this->method = $this->normalizeMethod($method);
        return $this;
    }

    public function getMethod(): string {
        if (null === $this->method) {
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

    public function isTraceMethod(): bool {
        return $this->getMethod() === self::TRACE_METHOD;
    }

    public function isConnectMethod(): bool {
        return $this->getMethod() === self::CONNECT_METHOD;
    }

    public function isPatchMethod(): bool {
        return $this->getMethod() === self::PATCH_METHOD;
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
}
