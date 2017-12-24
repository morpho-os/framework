<?php
declare(strict_types=1);
namespace Morpho\System\Web;

use function Morpho\Base\dasherize;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\Controller;
use Morpho\Web\NotFoundException;

class TestController extends Controller {
    public function indexAction() {
        $page = $this->newPage();
        $layout = $page->layout();
        $layout->setDirPath(dasherize($this->name()));
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