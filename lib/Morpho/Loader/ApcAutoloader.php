<?php
namespace Morpho\Loader;

abstract class ApcAutoloader extends BaseAutoloader {
    public function autoload($class) {
        $key = __CLASS__ . $class;
        $filePath = apc_fetch($key);
        if (false === $filePath) {
            apc_store($key, $this->findFile($class));
        }
    }

    abstract protected function findFile($class);
}
