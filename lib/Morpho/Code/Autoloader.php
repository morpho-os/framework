<?php
/**
 * This class uses parts of the code found in the ClassLoader class from the Composer project
 * (see Morpho\Base\ClassTypeAutoloader).
 */

//declare(strict_types=1);

namespace Morpho\Code;

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
     * @return string|false The path if found, false otherwise
     */
    abstract public function findFilePath(string $class);
}

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
function requireFile($filePath) {
    require $filePath;
}