<?php
namespace System\Controller;

use Morpho\Web\Controller;

class ErrorController extends Controller {
    public function pageNotFoundAction() {
        $this->request->getResponse()
            ->setStatusCode(404);
    }

    public function accessDeniedAction() {
        dd();
    }

    public function uncaughtErrorAction() {
        dd();
    }
}
