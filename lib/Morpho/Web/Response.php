<?php
namespace Morpho\Web;

use Zend\Http\PhpEnvironment\Response as BaseResponse;

class Response extends BaseResponse {
    public function redirect($uri, $sendAndExit = true, $httpStatusCode = null) {
        $this->getHeaders()->addHeaderLine('Location', $uri);
        $this->setStatusCode($httpStatusCode ?: self::STATUS_CODE_302);
        if ($sendAndExit) {
            $this->send();
            exit();
        }
    }

    public function isSuccessful(): bool {
        $code = $this->getStatusCode();
        // Use condition from jQuery: 304 == Not Modified.
        return $code >= self::STATUS_CODE_200 && $code < self::STATUS_CODE_300 || $code === self::STATUS_CODE_304;
    }
}
