<?php
namespace Morpho\Web;

use Zend\Http\Headers;
use Zend\Http\PhpEnvironment\Response as BaseResponse;

class Response extends BaseResponse {
    public function redirect($uri, $httpStatusCode = null) {
        $this->headers()->addHeaderLine('Location', (string)$uri);
        $this->setStatusCode($httpStatusCode ?: self::STATUS_CODE_302);
    }

    public function content() {
        return $this->content;
    }

    public function headers() {
        if ($this->headers === null || is_string($this->headers)) {
            // this is only here for fromString lazy loading
            $this->headers = (is_string($this->headers)) ? Headers::fromString($this->headers) : new Headers();
        }
        return $this->headers;
    }

    public function isContentEmpty(): bool {
        // @TODO: !isset($this->content[0]) is better??
        return $this->content == '';
    }

    public function isSuccessful(): bool {
        $code = $this->getStatusCode();
        // Use condition from jQuery: 304 == Not Modified.
        return $code >= self::STATUS_CODE_200 && $code < self::STATUS_CODE_300 || $code === self::STATUS_CODE_304;
    }
}