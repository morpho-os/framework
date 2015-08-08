<?php
namespace Morpho\Core;

use Morpho\Base\FileClassMapAutoloader;
use Morpho\Fs\Directory;

class ModuleAutoloader extends FileClassMapAutoloader implements \IteratorAggregate {
    public function __construct($moduleDirPath, $cacheDirPath, $useCache = true) {
        parent::__construct(
            Directory::create($cacheDirPath) . '/module-classmap.php',
            $moduleDirPath,
            null,
            $useCache
        );
    }

    public function getIterator() {
        return new \ArrayIterator($this->map);
    }
}
