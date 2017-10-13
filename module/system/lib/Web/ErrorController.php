<?php
namespace Morpho\System\Web;

use Morpho\Web\Controller;

class ErrorController extends Controller {
    public function notFoundAction() {
        /*
        $this->request->getResponse()
            ->setStatusCode(404);
        */
    }

    public function badRequestAction() {
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
