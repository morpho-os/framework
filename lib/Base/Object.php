<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types = 1);
namespace Morpho\Base;

use ReflectionObject;

abstract class Object extends \ArrayObject {
    private $reflected;

    private $classDirPath;

    public function namespace(): string {
        $class = get_class($this);
        return substr($class, 0, strrpos($class, '\\'));
    }

    public function classDirPath(): string {
        if (null === $this->classDirPath) {
            $this->classDirPath = dirname($this->classFilePath());
        }

        return $this->classDirPath;
    }

    public function classFilePath(): string {
        return str_replace('\\', '/', $this->reflect()->getFileName());
    }

    protected function reflect(): ReflectionObject {
        if (null === $this->reflected) {
            $this->reflected = new ReflectionObject($this);
        }
        return $this->reflected;
    }
}
