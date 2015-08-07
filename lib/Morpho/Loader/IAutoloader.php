<?php
namespace Morpho\Loader;

interface IAutoloader {
    /**
     * @return IAutoloader
     */
    public function register($prepend = false);

    /**
     * @return void
     */
    public function unregister();

    /**
     * @return string|false
     */
    public function autoload($class);

    public function findFilePath($class);
}
