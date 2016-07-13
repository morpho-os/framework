<?php
namespace Morpho\Code;

use Composer\Autoload\ClassLoader;

/**
 * Returns the first found Composer's autoloader.
 */
function composerAutoloader(): ClassLoader {
    foreach (spl_autoload_functions() as $callback) {
        if (is_array($callback) && $callback[0] instanceof ClassLoader && $callback[1] === 'loadClass') {
            return $callback[0];
        }
    }
    throw new \RuntimeException("Unable to find the Composer's autoloader in the list of autoloaders");
}