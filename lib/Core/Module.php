<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

class Module extends Node {
    protected $type = 'Module';

    /**
     * @var string
     */
    protected $moduleNamespace;

    public function __construct(string $name, string $moduleNamespace) {
        parent::__construct($name);
        $this->moduleNamespace = $moduleNamespace;
    }

    protected function childNameToClass(string $name) {
        if (false === strpos($name, '\\')) {
            // By default assume that it is a controller.
            $name = (PHP_SAPI == 'cli' ? 'Cli' : 'Web') . '\\' . $name . CONTROLLER_SUFFIX;
        }
        $class = $this->moduleNamespace . '\\' . $name;
        return class_exists($class) ? $class : false;
    }
}