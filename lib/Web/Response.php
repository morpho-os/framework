<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Core\IResponse;
use Zend\Http\Headers;
use Zend\Http\PhpEnvironment\Response as BaseResponse;

// @TODO: Merge with Morpho\Network\Http\HttpResponse

/**
 * @TODO: Add copyright from ZF.
 */
class Response implements IResponse {
    private $response;
    private $headers;

    // @TODO: Implement most useful codes, see https://developer.mozilla.org/en/docs/Web/HTTP/Status
    public const OK_STATUS_CODE = 200;
    public const FOUND_STATUS_CODE = 302;
    public const NOT_MODIFIED_STATUS_CODE = 304;
    public const BAD_REQUEST_STATUS_CODE = 400;
    public const FORBIDDEN_STATUS_CODE = 403;
    public const NOT_FOUND_STATUS_CODE = 404;
    public const INTERNAL_SERVER_ERROR_STATUS_CODE = 500;

    public function __construct() {
        $this->response = new BaseResponse();
    }

    public function redirect($uri, int $httpStatusCode = null): void {
        $this->headers()->addHeaderLine('Location', (string)$uri);
        $this->setStatusCode($httpStatusCode ?: self::FOUND_STATUS_CODE);
    }

    public function setStatusCode(int $statusCode): void {
        $this->response->setStatusCode($statusCode);
    }

    public function statusCode(): int {
        return $this->response->getStatusCode();
    }

    public function setContent(string $content): void {
        $this->response->setContent($content);
    }

    public function content() {
        return $this->response->getContent();
    }

    public function isRedirect(): bool {
        return $this->response->isRedirect();
    }

    public function headers() {
        if ($this->headers === null) {
            // @TODO: Eliminate the dependency from ZF Headers??
            $this->headers = new Headers();
        }
        return $this->headers;
    }

    public function isContentEmpty(): bool {
        return $this->response->getContent() == '';
    }

    public function isSuccess(): bool {
        // Use condition from jQuery.
        return $this->response->isSuccess() || $this->statusCode() === self::NOT_MODIFIED_STATUS_CODE;
    }

    public function send(): void {
        $this->response->send();
    }
}
