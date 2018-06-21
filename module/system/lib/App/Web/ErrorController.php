<?php declare(strict_types=1);
namespace Morpho\System\App\Web;

use Morpho\App\Web\Controller;

class ErrorController extends Controller {
    public function badRequestAction() {
        $this->request->response()->setStatusCode(400);
    }

    public function forbiddenAction() {
        $this->request->response()->setStatusCode(403);
    }

    public function notFoundAction() {
        $this->request->response()->setStatusCode(404);
    }

    public function uncaughtAction() {
        $this->request->response()->setStatusCode(500);
    }
}
