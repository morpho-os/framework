<?php
namespace Morpho\Loader;

abstract class BaseAutoloader implements IAutoloader {
    public function autoload($class) {
        $filePath = $this->findFilePath($class);
        if ($filePath) {
            require $filePath;

            return $class;
        }

        return false;
    }

    public function register($prepend = false) {
        spl_autoload_register(array($this, 'autoload'), true, $prepend);

        return $this;
    }

    public function unregister() {
        spl_autoload_unregister(array($this, 'autoload'));
    }
}
