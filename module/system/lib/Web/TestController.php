<?php
declare(strict_types=1);
namespace Morpho\System\Web;

use function Morpho\Base\dasherize;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\Controller;
use Morpho\Web\NotFoundException;
use Morpho\Web\View\View;

class TestController extends Controller {
    public function indexAction() {
        $moduleName = $this->parentByType('Module')->name();
        $moduleIndex = $this->serviceManager->get('moduleIndex');
        $viewDirPath = $moduleIndex->moduleMeta($moduleName)['paths']['viewDirPath'];
        $controllerViewDirPath = $viewDirPath . '/' . dasherize($this->name());
        $layout = new View('test');
        $layout->setDirPath($controllerViewDirPath);
        $this->setLayout($layout);
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