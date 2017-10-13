<?php
declare(strict_types=1);
namespace Morpho\System\Web;

use function Morpho\Base\dasherize;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\Controller;
use Morpho\Web\NotFoundException;
use Morpho\Web\View\IWithThemeModule;

class TestController extends Controller {
    public function indexAction() {
        $moduleManager = $this->parent('ModuleManager');
        $controllerViewDirPath = $this->parentByType('Module')->fs()->viewDirPath() . '/' . dasherize($this->name());
        $found = false;
        foreach ($moduleManager->enabledModuleNames() as $moduleName) {
            $module = $moduleManager->offsetGet($moduleName);
            if ($module instanceof IWithThemeModule) {
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