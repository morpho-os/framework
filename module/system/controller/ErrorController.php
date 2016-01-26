<?php
namespace System\Controller;

use Morpho\Web\Controller;

class ErrorController extends Controller {
    public function pageNotFoundAction() {
        $this->request->getResponse()
            ->setStatusCode(404);
    }

    public function accessDeniedAction() {
    }

    public function uncaughtErrorAction() {
        /*
        $exception = $this->request->getInternalParam('error')
        d($exception->getMessage(), $exception->getFile(), $exception->getLine());
        */
    }
}
