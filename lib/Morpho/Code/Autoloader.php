<?php
//declare(strict_types=1);

namespace Morpho\Code;

use function Morpho\Base\requireFile;

abstract class Autoloader {
    public function autoload(string $class): bool {
        $filePath = $this->findFilePath($class);
        if ($filePath) {
            requireFile($filePath);
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    public function register(bool $prepend = false) {
        spl_autoload_register([$this, 'autoload'], true, $prepend);
    }

    public function unregister() {
        spl_autoload_unregister([$this, 'autoload']);
    }

    /**
     * @return string|false The path (string) if found, any value which can be converted to the false.
     */
    abstract public function findFilePath(string $class);
}