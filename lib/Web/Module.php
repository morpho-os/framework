<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use const Morpho\Core\CONTROLLER_SUFFIX;
use Morpho\Core\Module as BaseModule;

class Module extends BaseModule {
    protected function childNameToClass(string $name) {
        if (false === strpos($name, '\\')) {
            // By default assume that it is a controller.
            //$name = (PHP_SAPI === 'cli' ? 'Cli' : 'Web') . '\\' . $name . CONTROLLER_SUFFIX;
            $name = 'Web\\' . $name . CONTROLLER_SUFFIX;
        }
        $moduleNs = $this->moduleIndex->moduleMeta($this->name())['namespace'];
        $class = $moduleNs . '\\' . $name;
        return class_exists($class) ? $class : false;
    }
}