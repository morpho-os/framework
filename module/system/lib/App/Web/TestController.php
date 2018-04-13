<?php declare(strict_types=1);
namespace Morpho\System\App\Web;

use function Morpho\Base\dasherize;
use Morpho\App\Web\AccessDeniedException;
use Morpho\App\Web\BadRequestException;
use Morpho\App\Web\Controller;
use Morpho\App\Web\NotFoundException;

class TestController extends Controller {
    public function indexAction() {
        $page = $this->newPage();
        $layout = $page->layout();
        $layout->setDirPath(dasherize($this->request->controllerName()));
        $layout->setName('test');
        return $page;
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
