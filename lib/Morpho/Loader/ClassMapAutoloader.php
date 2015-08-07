<?php
namespace Morpho\Loader;

abstract class ClassMapAutoloader extends BaseAutoloader {
    protected $map;

    public function clearMap() {
        $this->map = null;
    }

    public function findFilePath($class) {
        if (null === $this->map) {
            if (!$this->isMapCanBeCreated()) {
                return;
            }
            $this->map = $this->createMap();
        }
        if (substr($class, 0, 1) == '\\') {
            $class = substr($class, 1);    // some versions of PHP has a bug that adds '\\' to beginning of class.
        }
        $filePath = false;
        if (isset($this->map[$class])) {
            $filePath = $this->map[$class];
        }

        return $filePath;
    }

    protected function isMapCanBeCreated() {
        return true;
    }

    /**
     * @return array
     */
    abstract protected function createMap();
}
