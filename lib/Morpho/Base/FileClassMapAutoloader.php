<?php
declare(strict_types=1);

namespace Morpho\Base;

use Morpho\Code\ClassDiscoverer;
use Morpho\Fs\File;

class FileClassMapAutoloader extends Autoloader {
    protected $regexp;

    protected $searchDirPaths;

    protected $mapFilePath;

    protected $useCache = true;

    protected $map;

    /**
     * @param string|null $mapFilePath
     * @param array|string $searchDirPaths
     * @param string|\Closure $regexp
     */
    public function __construct($mapFilePath, $searchDirPaths, $regexp = null, bool $useCache = true) {
        $this->mapFilePath = $mapFilePath;
        $this->searchDirPaths = $searchDirPaths;
        $this->regexp = $regexp;
        $this->useCache = $useCache;
    }

    public function findFilePath(string $class) {
        if (null === $this->map) {
            $this->map = $this->createMap();
        }
        return isset($this->map[$class]) ? $this->map[$class] : false;
    }

    public function clearMap() {
        $this->map = null;
        if (is_file($this->mapFilePath)) {
            File::delete($this->mapFilePath);
        }
    }

    /**
     * @param null|bool $flag
     */
    public function useCache($flag = null): bool {
        if (null !== $flag) {
            $this->useCache = $flag;
        }
        return $this->useCache;
    }

    protected function createMap(): array {
        $useCache = $this->useCache;
        if ($useCache && is_file($this->mapFilePath)) {
            return require $this->mapFilePath;
        }
        $classDiscoverer = new ClassDiscoverer();
        $map = $classDiscoverer->getClassesForDir($this->searchDirPaths, $this->regexp);
        if ($useCache) {
            File::write($this->mapFilePath, '<?php return ' . var_export($map, true) . ';');
        }

        return $map;
    }
}