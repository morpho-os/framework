<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\App\Core\Response as BaseResponse;

/**
 * This class based on \Zend\Http\PhpEnvironment\Response class.
 * @see https://github.com/zendframework/zend-http for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license https://github.com/zendframework/zend-http/blob/master/LICENSE.md New BSD License
 */
class Response extends BaseResponse {
    /**
     * @var int
     */
    protected $statusCode = self::OK_STATUS_CODE;

    /**
     * @var null|\ArrayObject
     */
    private $headers;

    /**
     * @var ?string
     */
    private $statusLine;

    // @TODO: Move to StatusCode::OK
    public const OK_STATUS_CODE = 200;
    public const FOUND_STATUS_CODE = 302;
    public const NOT_MODIFIED_STATUS_CODE = 304;
    public const BAD_REQUEST_STATUS_CODE = 400;
    public const FORBIDDEN_STATUS_CODE = 403;
    public const NOT_FOUND_STATUS_CODE = 404;
    public const INTERNAL_SERVER_ERROR_STATUS_CODE = 500;

    /**
     * @param string|Uri\Uri $uri
     */
    public function redirect($uri, int $statusCode = null): self {
        $this->headers()->offsetSet('Location', \is_string($uri) ? $uri : $uri->toStr(true));
        $this->setStatusCode($statusCode ?: self::FOUND_STATUS_CODE);
        return $this;
    }

    public function setStatusLine(string $statusLine): void {
        $this->statusLine = $statusLine;
    }

    public function statusLine(): string {
        if (null == $this->statusLine){
            $this->statusLine = $this->statusCodeToStatusLine($this->statusCode);
        }
        return $this->statusLine;
    }

    public function headers(): \ArrayObject {
        if ($this->headers === null) {
            $this->headers = new \ArrayObject();
        }
        return $this->headers;
    }

    public function isRedirect(): bool {
        $statusCode = $this->statusCode;
        return isset($this->headers()['Location'])
            && 300 <= $statusCode && $statusCode < 400;
    }

    public function isSuccess(): bool {
        $statusCode = $this->statusCode;
        return 200 <= $statusCode && $statusCode < 400;
    }

    public function send(): void {
        $this->sendStatusLine();
        $this->sendHeaders();
        parent::send();
    }

    public function statusCodeToStatusLine(int $statusCode): string {
        return Environment::httpVersion() . ' ' . \intval($statusCode) . ' ' . $this->statusCodeToReason($statusCode);
    }

    public function statusCodeToReason(int $statusCode): string {
        // http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
        switch ($statusCode) {
            case self::OK_STATUS_CODE:
                $reasonPhrase = 'OK';
                break;
            case self::FOUND_STATUS_CODE:
                $reasonPhrase = 'Found';
                break;
            case self::NOT_MODIFIED_STATUS_CODE:
                $reasonPhrase = 'Not Modified';
                break;
            case self::BAD_REQUEST_STATUS_CODE:
                $reasonPhrase = 'Bad Request';
                break;
            case self::FORBIDDEN_STATUS_CODE:
                $reasonPhrase = 'Forbidden';
                break;
            case self::NOT_FOUND_STATUS_CODE:
                $reasonPhrase = 'Not Found';
                break;
            case self::INTERNAL_SERVER_ERROR_STATUS_CODE:
                $reasonPhrase = 'Internal Server Error';
                break;
            case 100:
                $reasonPhrase = 'Continue';
                break;
            case 101:
                $reasonPhrase = 'Switching Protocols';
                break;
            case 102:
                $reasonPhrase = 'Processing';
                break;
            case 103:
                $reasonPhrase = 'Early Hints';
                break;
            case 201:
                $reasonPhrase = 'Created';
                break;
            case 202:
                $reasonPhrase = 'Accepted';
                break;
            case 203:
                $reasonPhrase = 'Non-Authoritative Information';
                break;
            case 204:
                $reasonPhrase = 'No Content';
                break;
            case 205:
                $reasonPhrase = 'Reset Content';
                break;
            case 206:
                $reasonPhrase = 'Partial Content';
                break;
            case 207:
                $reasonPhrase = 'Multi-Status';
                break;
            case 208:
                $reasonPhrase = 'Already Reported';
                break;
            case 226:
                $reasonPhrase = 'IM Used';
                break;
            case 300:
                $reasonPhrase = 'Multiple Choices';
                break;
            case 301:
                $reasonPhrase = 'Moved Permanently';
                break;
            case 303:
                $reasonPhrase = 'See Other';
                break;
            case 305:
                $reasonPhrase = 'Use Proxy';
                break;
            case 306:
                $reasonPhrase = '(Unused)';
                break;
            case 307:
                $reasonPhrase = 'Temporary Redirect';
                break;
            case 308:
                $reasonPhrase = 'Permanent Redirect';
                break;
            case 401:
                $reasonPhrase = 'Unauthorized';
                break;
            case 402:
                $reasonPhrase = 'Payment Required';
                break;
            case 405:
                $reasonPhrase = 'Method Not Allowed';
                break;
            case 406:
                $reasonPhrase = 'Not Acceptable';
                break;
            case 407:
                $reasonPhrase = 'Proxy Authentication Required';
                break;
            case 408:
                $reasonPhrase = 'Request Timeout';
                break;
            case 409:
                $reasonPhrase = 'Conflict';
                break;
            case 410:
                $reasonPhrase = 'Gone';
                break;
            case 411:
                $reasonPhrase = 'Length Required';
                break;
            case 412:
                $reasonPhrase = 'Precondition Failed';
                break;
            case 413:
                $reasonPhrase = 'Payload Too Large';
                break;
            case 414:
                $reasonPhrase = 'URI Too Long';
                break;
            case 415:
                $reasonPhrase = 'Unsupported Media Type';
                break;
            case 416:
                $reasonPhrase = 'Range Not Satisfiable';
                break;
            case 417:
                $reasonPhrase = 'Expectation Failed';
                break;
            case 421:
                $reasonPhrase = 'Misdirected Request';
                break;
            case 422:
                $reasonPhrase = 'Unprocessable Entity';
                break;
            case 423:
                $reasonPhrase = 'Locked';
                break;
            case 424:
                $reasonPhrase = 'Failed Dependency';
                break;
            case 425:
                $reasonPhrase = 'Unassigned';
                break;
            case 426:
                $reasonPhrase = 'Upgrade Required';
                break;
            case 427:
                $reasonPhrase = 'Unassigned';
                break;
            case 428:
                $reasonPhrase = 'Precondition Required';
                break;
            case 429:
                $reasonPhrase = 'Too Many Requests';
                break;
            case 430:
                $reasonPhrase = 'Unassigned';
                break;
            case 431:
                $reasonPhrase = 'Request Header Fields Too Large';
                break;
            case 451:
                $reasonPhrase = 'Unavailable For Legal Reasons';
                break;
            case 501:
                $reasonPhrase = 'Not Implemented';
                break;
            case 502:
                $reasonPhrase = 'Bad Gateway';
                break;
            case 503:
                $reasonPhrase = 'Service Unavailable';
                break;
            case 504:
                $reasonPhrase = 'Gateway Timeout';
                break;
            case 505:
                $reasonPhrase = 'HTTP Version Not Supported';
                break;
            case 506:
                $reasonPhrase = 'Variant Also Negotiates';
                break;
            case 507:
                $reasonPhrase = 'Insufficient Storage';
                break;
            case 508:
                $reasonPhrase = 'Loop Detected';
                break;
            case 509:
                $reasonPhrase = 'Unassigned';
                break;
            case 510:
                $reasonPhrase = 'Not Extended';
                break;
            case 511:
                $reasonPhrase = 'Network Authentication Required';
                break;
            default:
                if (104 <= $statusCode && $statusCode <= 199) {
                    $reasonPhrase = 'Unassigned';
                } elseif (209 <= $statusCode && $statusCode <= 225) {
                    $reasonPhrase = 'Unassigned';
                } elseif (227 <= $statusCode && $statusCode <= 299) {
                    $reasonPhrase = 'Unassigned';
                } elseif (309 <= $statusCode && $statusCode <= 399) {
                    $reasonPhrase = 'Unassigned';
                } elseif (418 <= $statusCode && $statusCode <= 420) {
                    $reasonPhrase = 'Unassigned';
                } elseif (432 <= $statusCode && $statusCode <= 450) {
                    $reasonPhrase = 'Unassigned';
                } elseif (452 <= $statusCode && $statusCode <= 499) {
                    $reasonPhrase = 'Unassigned';
                } elseif (512 <= $statusCode && $statusCode <= 599) {
                    $reasonPhrase = 'Unassigned';
                } else {
                    throw new \RuntimeException("Unable to map the status code to the reason phrase");
                }
        }
        return $reasonPhrase;
    }

    protected function sendStatusLine(): void {
        // @TODO: http_response_code
        $this->sendHeader($this->statusLine());
    }

    protected function sendHeaders(): void {
        foreach ($this->headers() as $name => $value) {
            $this->sendHeader($name . ': ' . $value);
        }
    }

    protected function sendHeader(string $value): void {
        \header($value);
    }
}
