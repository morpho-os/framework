<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Core;

use Morpho\Caching\Cache;
use Psr\SimpleCache\CacheInterface as ICache;

class ModuleIndexer implements IModuleIndexer {
    private $moduleMetaProvider;
    private $cache;
    private $cacheKey;

    public function __construct(iterable $moduleMetaProvider, ICache $cache) {
        $this->moduleMetaProvider = $moduleMetaProvider;
        $this->cache = $cache;
        $this->cacheKey = Cache::normalizeKey(__METHOD__);
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
        foreach ($this->moduleMetaProvider as $moduleMeta) {
            $index[$moduleMeta['name']] = $moduleMeta;
        }
        uasort($index, function ($a, $b) {
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
}