<?php declare(strict_types=1);
namespace Morpho\Site\Localhost\App\Web;

use Morpho\App\Web\Controller;

class TestController extends Controller {
    public function indexAction() {
        $this->setParentViewResult('test/test');
    }

    public function status400Action() {
        return $this->mkBadRequestResult();
    }

    public function status403Action() {
        return $this->mkForbiddenResult();
    }

    public function status404Action() {
        return $this->mkNotFoundResult();
    }

    public function status405Action() {
        // For testing clients should send: POST $prefix/test/status405
    }

    public function status500Action() {
        throw new \RuntimeException();
    }
}
