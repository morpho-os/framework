<?php
namespace Morpho\Web;

use Zend\Http\PhpEnvironment\Response as BaseResponse;

class Response extends BaseResponse {
    public function redirect($uri, $httpStatusCode = null) {
        $this->getHeaders()->addHeaderLine('Location', (string)$uri);
        $this->setStatusCode($httpStatusCode ?: self::STATUS_CODE_302);
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
