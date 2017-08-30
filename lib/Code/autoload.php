<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code;

use Composer\Autoload\ClassLoader;
use PhpParser\ParserFactory;

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

function parseFile(string $filePath): ?array {
    return parse(file_get_contents($filePath));
}

function parse(string $str): ?array {
    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    return $parser->parse($str);
}