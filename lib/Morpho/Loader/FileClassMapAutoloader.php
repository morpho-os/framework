<?php
namespace Morpho\Loader;

use Morpho\Code\ClassDiscoverer;
use Morpho\Fs\File;

class FileClassMapAutoloader extends ClassMapAutoloader {
    protected $regexp;

    protected $searchDirPaths;

    protected $mapFilePath;

    protected $useCache = true;

    /**
     * @param string|null $mapFilePath
     * @param array|string $searchDirPaths
     * @param string|\Closure $regexp
     * @param bool $useCache
     */
    public function __construct($mapFilePath, $searchDirPaths, $regexp = null, $useCache = true) {
        $this->mapFilePath = $mapFilePath;
        $this->searchDirPaths = $searchDirPaths;
        $this->regexp = $regexp;
        $this->useCache = $useCache;
    }

    public function clearMap() {
        parent::clearMap();
        if (is_file($this->mapFilePath)) {
            File::delete($this->mapFilePath);
        }
    }

    /**
     * @param null|bool $flag
     * @return bool
     */
    public function useCache($flag = null) {
        if (null !== $flag) {
            $this->useCache = $flag;
        }
        return $this->useCache;
    }

    protected function createMap() {
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
