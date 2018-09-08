<?php declare(strict_types=1);
namespace Morpho\Site\Localhost\App\Web;

use function Morpho\Base\dasherize;
use Morpho\App\Web\Controller;
use Morpho\Base\NotImplementedException;

class TestController extends Controller {
    public function indexAction() {
        $page = $this->mkViewResult('test');
        $page->setDirPath(dasherize($this->request->controllerName()));
        return $this->mkViewResult(null, null, $page);
    }

/*    public function nullActionResultAction() {
        d('test');
        throw new NotImplementedException();
    }*/

    public function responseActionResultAction() {
        throw new NotImplementedException();
    }

    public function jsonActionResultAction() {
        throw new NotImplementedException();
    }

    public function xmlActionResultAction() {
        throw new NotImplementedException();
    }

    public function viewActionResultAction() {
        throw new NotImplementedException();
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

    public function status500Action() {
        throw new \RuntimeException();
    }
}
