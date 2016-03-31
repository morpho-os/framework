<?php
//declare(strict_types = 1);
namespace Morpho\Base;

use Morpho\Code\ClassDiscoverer;
use Morpho\Fs\File;

class ClassMapClassLoader extends Autoloader {
    protected $processor;

    protected $searchDirPaths;

    protected $mapFilePath;

    protected $useCache = true;

    protected $map;

    /**
     * @param array|string|null $searchDirPaths
     * @param string|\Closure $processor
     */
    public function __construct(string $mapFilePath = null, $searchDirPaths = null, $processor = null, bool $useCache = true) {
        $this->mapFilePath = $mapFilePath;
        $this->searchDirPaths = $searchDirPaths;
        $this->processor = $processor;
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
        $map = $classDiscoverer->getClassMapForDir($this->searchDirPaths, $this->processor, ['followSymlinks' => true]);
        if ($useCache) {
            File::write($this->mapFilePath, '<?php return ' . var_export($map, true) . ';');
        }

        return $map;
    }
}