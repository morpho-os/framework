<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php\Autoloading;

use function Morpho\Base\requireFile;

abstract class Autoloader {
    public function autoload(string $class): bool {
        $filePath = $this->filePath($class);
        if ($filePath) {
            requireFile($filePath);
            return true;
        }
        return false;
    }

    public function register(bool $prepend = false): void {
        \spl_autoload_register([$this, 'autoload'], true, $prepend);
    }

    public function unregister(): void {
        \spl_autoload_unregister([$this, 'autoload']);
    }

    /**
     * @return string|false The path of class (string) or false otherwise.
     */
    abstract public function filePath(string $class);
}
