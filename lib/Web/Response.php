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
class Response extends BaseResponse implements IResponse {
    public function redirect($uri, $httpStatusCode = null): void {
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

    public function isSuccess(): bool {
        // Use condition from jQuery: 304 == Not Modified.
        return parent::isSuccess() || $this->getStatusCode() === self::STATUS_CODE_304;
    }

    /**
     * @TODO: Remove this method after switching to >= PHP 7.2
     */
    public function send(): IResponse {
        parent::send();
        return $this;
    }
}
