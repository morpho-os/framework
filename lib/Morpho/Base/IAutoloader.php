<?php
declare(strict_types=1);

namespace Morpho\Base;

interface IAutoloader {
    public function register(bool $prepend = false);

    /**
     * @return void
     */
    public function unregister();

    /**
     * @return string|false
     */
    public function autoload(string $class);

    /**
     * @return string|null The path if found, false otherwise
     */
    public function findFilePath(string $class);
}
