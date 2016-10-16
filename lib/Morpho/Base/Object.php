<?php
declare(strict_types = 1);

namespace Morpho\Base;

abstract class Object {
    private $reflected;

    private $classDirPath;

    public function getNamespace(): string {
        $class = get_class($this);
        return substr($class, 0, strrpos($class, '\\'));
    }

    public function getClassDirPath(): string {
        if (null === $this->classDirPath) {
            $this->classDirPath = dirname($this->getClassFilePath());
        }

        return $this->classDirPath;
    }

    public function getClassFilePath(): string {
        return str_replace('\\', '/', $this->reflect()->getFileName());
    }

    protected function reflect(): \ReflectionObject {
        if (null === $this->reflected) {
            $this->reflected = new \ReflectionObject($this);
        }
        return $this->reflected;
    }
}
