<?php declare(strict_types=1);
namespace Morpho\System\App\Web;

use function Morpho\Base\dasherize;
use Morpho\App\Web\AccessDeniedException;
use Morpho\App\Web\BadRequestException;
use Morpho\App\Web\Controller;
use Morpho\App\Web\NotFoundException;
use Morpho\Base\NotImplementedException;

class TestController extends Controller {
    public function indexAction() {
        $page = $this->mkView('test');
        $page->setDirPath(dasherize($this->request->controllerName()));
        return $this->mkView(null, null, $page);
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
        throw new BadRequestException();
    }

    public function status403Action() {
        throw new AccessDeniedException();
    }

    public function status404Action() {
        throw new NotFoundException();
    }

    public function status500Action() {
        throw new \RuntimeException();
    }
}
