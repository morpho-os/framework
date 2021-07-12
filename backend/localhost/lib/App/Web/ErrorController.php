<?php declare(strict_types=1);
namespace Morpho\Site\Localhost\App\Web;

use Morpho\App\Web\Controller;

/**
 * @noRoutes
 */
class ErrorController extends Controller {
    public function badRequest() {
        $this->request()->response()->setStatusCode(400);
    }

    public function forbidden() {
        $this->request()->response()->setStatusCode(403);
    }

    public function notFound() {
        $this->request()->response()->setStatusCode(404);
    }

    public function uncaught() {
        $this->request()->response()->setStatusCode(500);
    }

    public function methodNotAllowed() {
        $this->request()->response()->setStatusCode(405);
    }
}
