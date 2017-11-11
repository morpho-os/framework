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

    public function uncaughtAction() {
        /*
        $exception = $this->request->param('error')
        d($exception->getMessage(), $exception->getFile(), $exception->getLine());
        */
    }
}
