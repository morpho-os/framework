<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Autoloading;

use Composer\Autoload\ClassLoader;

/**
 * Returns the first found Composer's autoloader - an instance of the \Composer\Autoloader\ClassLoader.
 */
function composerAutoloader(): ClassLoader {
    foreach (spl_autoload_functions() as $callback) {
        if (is_array($callback) && $callback[0] instanceof ClassLoader && $callback[1] === 'loadClass') {
            return $callback[0];
        }
    }
    throw new \RuntimeException("Unable to find the Composer's autoloader in the list of autoloaders");
}
