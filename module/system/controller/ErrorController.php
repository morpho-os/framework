<?php
namespace System\Controller;

use Morpho\Web\Controller;

class ErrorController extends Controller {
    public function notFoundAction() {
        $this->request->getResponse()
            ->setStatusCode(404);
    }
}
