<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Autoloading;

use Morpho\Caching\ICache;
use Morpho\Code\Reflection\ClassTypeDiscoverer;
use function Morpho\Caching\cacheKey;

class ClassTypeMapAutoloader extends Autoloader {
    protected $processor;

    protected $searchDirPaths;

    protected ?array $map = null;

    protected ?ICache $cache;

    protected string $cacheKey;

    /**
     * @param array|string|null $searchDirPaths
     * @param string|\Closure $processor
     * @param ICache|null $cache
     */
    public function __construct($searchDirPaths = null, $processor = null, ICache $cache = null) {
        $this->searchDirPaths = $searchDirPaths;
        $this->processor = $processor;
        $this->cache = $cache;
        $this->cacheKey = cacheKey($this, __FUNCTION__);
    }

    /**
     * @param string $class
     * @return string|false
     */
    public function filePath(string $class) {
        if (null === $this->map) {
            $useCache = null !== $this->cache;
            if ($useCache) {
                if (!$this->cache->has($this->cacheKey)) {
                    $this->map = $this->mkMap();
                    $this->cache->set($this->cacheKey, $this->map);
                } else {
                    $this->map = $this->cache->get($this->cacheKey);
                }
            } else {
                $this->map = $this->mkMap();
            }
        }
        return isset($this->map[$class]) ? $this->map[$class] : false;
    }

    public function clearMap(): void {
        $this->map = null;
        if (null !== $this->cache) {
            $this->cache->clear();
        }
    }

    protected function mkMap(): array {
        $classTypeDiscoverer = new ClassTypeDiscoverer();
        return $classTypeDiscoverer->classTypesDefinedInDir($this->searchDirPaths, $this->processor, ['followLinks' => true]);
    }
}
