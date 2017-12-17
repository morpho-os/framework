<?php //declare(strict_types = 1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use Morpho\Fs\Path;
use ReflectionObject;

abstract class ArrayObject extends \ArrayObject {
    /**
     * @var ?bool
     */
    private $reflected;

    /**
     * @var ?string
     */
    private $classDirPath;

    /**
     * @var ?string
     */
    private $classFilePath;

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
        if (null === $this->classFilePath) {
            $this->classFilePath = Path::normalize($this->reflect()->getFileName());
        }
        return $this->classFilePath;
    }

    protected function reflect(): ReflectionObject {
        if (null === $this->reflected) {
            $this->reflected = new ReflectionObject($this);
        }
        return $this->reflected;
    }
}
