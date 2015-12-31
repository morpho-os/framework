<?php
namespace Morpho\Core;

use Morpho\Base\FileClassMapAutoloader;
use function Morpho\Base\head;
use Morpho\Fs\Directory;
use Morpho\Fs\Path;

class ModuleClassLoader extends FileClassMapAutoloader implements \IteratorAggregate {
    public function __construct($moduleDirPath, $cacheDirPath, $useCache = true) {
        parent::__construct(
            Directory::create($cacheDirPath) . '/module-classmap.php',
            $moduleDirPath,
            function ($path, $isDir) {
                if ($isDir) {
                    // Skip the "MODULE_DIR_PATH/$moduleName/vendor" directories (libraries managed by Composer).
                    if (0 === strpos($path, MODULE_DIR_PATH) && strlen($path) > strlen(MODULE_DIR_PATH)) {
                        $moduleName = head(Path::toRelative(MODULE_DIR_PATH, $path), '/');
                        return false === strpos($path, MODULE_DIR_PATH . '/' . $moduleName . '/vendor');
                    }
                    return true;
                }
                return preg_match('/\.php$/', $path);
            },
            $useCache
        );
    }

    public function getIterator() {
        if (null === $this->map) {
            $this->map = $this->createMap();
        }
        return new \ArrayIterator($this->map);
    }
}