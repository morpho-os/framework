<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

use Morpho\Caching\ICache;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\IServiceManager;

class ModuleIndexer implements IModuleIndexer, IHasServiceManager {
    private $cache;
    private $cacheKey;
    private $serviceManager;

    public function __construct(ICache $cache, string $cacheKey = null) {
        $this->cache = $cache;
        $this->cacheKey = $cacheKey ?? \get_class($this) . '::' . __FUNCTION__;
    }

    /**
     * Indexes all modules and returns the index. Can cache the result.
     * @return array|\ArrayAccess
     */
    public function index() {
        $cacheKey = $this->cacheKey;
        $index = $this->cache->get($cacheKey);
        if (null !== $index) {
            return $index;
        }
        $index = [];
        foreach ($this->serviceManager['moduleIterator'] as $module) {
            $index[$module['name']] = $module;
        }
        \uasort($index, function ($a, $b) {
            return $a['weight'] - $b['weight'];
        });
        $this->cache->set($cacheKey, $index);
        return $index;
    }

    /**
     * Clears the internal state and cache so that the next call of the index() will build a new index.
     */
    public function clear(): void {
        $this->cache->delete($this->cacheKey);
    }

    public function setServiceManager(IServiceManager $serviceManager): void {
        $this->serviceManager = $serviceManager;
    }
}
