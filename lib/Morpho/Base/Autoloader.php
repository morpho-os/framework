<?php
/**
 * This class uses parts of the code found in the ClassLoader class from the Composer project
 * (see Morpho\Base\ClassLoader).
 */

declare(strict_types=1);

namespace Morpho\Base;

abstract class Autoloader implements IAutoloader {
    public function autoload(string $class) {
        $filePath = $this->findFilePath($class);
        if ($filePath) {
            requireFile($filePath);

            return true;
        }

        return false;
    }

    public function register(bool $prepend = false) {
        spl_autoload_register([$this, 'autoload'], true, $prepend);

        return $this;
    }

    public function unregister() {
        spl_autoload_unregister([$this, 'autoload']);
    }
}


/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
function requireFile($filePath) {
    require $filePath;
}