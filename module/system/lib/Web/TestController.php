<?php
declare(strict_types=1);
namespace Morpho\System\Web;

use function Morpho\Base\dasherize;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\Controller;
use Morpho\Web\NotFoundException;
use Morpho\Web\View\IHasTheme;

class TestController extends Controller {
    public function indexAction() {
        $controllerViewDirPath = $this->parentByType('Module')->pathManager()->viewDirPath() . '/' . dasherize($this->name());
        $found = false;
        $moduleProvider = $this->serviceManager->get('moduleProvider');
        foreach ($this->serviceManager->get('site')->config()['modules'] as $moduleName => $_) {
            $module = $moduleProvider->offsetGet($moduleName);
            if ($module instanceof IHasTheme) {
                $module->theme()->addBaseDirPath($controllerViewDirPath);
                $found = true;
            }
        }
        if (!$found) {
            throw new \RuntimeException("Unable to find a theme");
        }
        $this->setLayout('test');
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