<?php declare(strict_types=1);
namespace Morpho\App\Web;

use const Morpho\App\CONTROLLER_SUFFIX;
use Morpho\App\InstanceProvider as BaseInstanceProvider;

class InstanceProvider extends BaseInstanceProvider {
    protected function controllerClassWithoutModuleNs(string $controllerName): string {
        return 'App\\Web\\' . $controllerName . CONTROLLER_SUFFIX;
    }
}
