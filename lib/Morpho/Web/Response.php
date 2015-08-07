<?php
namespace Morpho\Web;

use Zend\Http\PhpEnvironment\Response as BaseResponse;

class Response extends BaseResponse {
    public function redirect($uri, $sendAndExit = true, $httpStatusCode = null) {
        $this->getHeaders()->addHeaderLine('Location', $uri);
        $this->setStatusCode($httpStatusCode ?: 302);
        if ($sendAndExit) {
            $this->send();
            exit();
        }
    }

    public function isSuccess() {
        $code = $this->getStatusCode();
        // Use condition from jQuery: 304 == Not Modified.
        return $code >= 200 && $code < 300 || $code === 304;
    }
}
