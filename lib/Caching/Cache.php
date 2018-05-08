<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

/**
 * This class based on \Doctrine\Common\Cache\CacheProvider from Doctrine project (MIT license).
 * For more information, see <http://www.doctrine-project.org>.
 * Copyright (c) 2006-2015 Doctrine Project
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 * @author Benoit Burnichon <bburnichon@gmail.com>
 */
/**
 * This class based on https://github.com/Roave/DoctrineSimpleCache/blob/master/src/SimpleCacheAdapter.php
 * The MIT License (MIT)
 * Copyright (c) 2017 Roave, LLC.
 */
abstract class Cache implements ICache {
    protected const STATS_HITS             = 'hits';
    protected const STATS_MISSES           = 'misses';
    protected const STATS_UPTIME           = 'uptime';
    protected const STATS_MEMORY_USAGE     = 'memory_usage';
    protected const STATS_MEMORY_AVAILABLE = 'memory_available';

    public function get($key, $default = null) {
        [$found, $value] = $this->fetch($key);
        return $found ? $value : $default;
    }

    public function set($key, $value, $ttl = null): bool {
        if ($ttl === null) {
            $ttl = 0;
        } else {
            if ($ttl instanceof \DateInterval) {
                $ttl = $this->dateIntervalToInt($ttl);
            }
            if (!\is_int($ttl)) {
                throw new \InvalidArgumentException('Invalid ttl');
            }
            if ($ttl <= 0) {
                return $this->delete($key);
            }
        }
        return $this->save($key, $value, $ttl);
    }

    /**
     * @param array|\Traversable $keys
     * @param mixed $default
     * @return array
     */
    public function getMultiple($keys, $default = null): array {
        return \array_merge(\array_fill_keys($keys, $default), $this->fetchMultiple($keys));
    }

    /**
     * @param iterable $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool {
        if ($ttl === null) {
            $ttl = 0;
        } else {
            if ($ttl instanceof \DateInterval) {
                $ttl = $this->dateIntervalToInt($ttl);
            }
            if (!\is_int($ttl)) {
                throw new \InvalidArgumentException('Invalid ttl');
            }
            if ($ttl <= 0) {
                return $this->deleteMultiple(\array_keys($values));
            }
        }
        return $this->saveMultiple($values, $ttl);
    }

    public function deleteMultiple($keys): bool {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * @return array a tuple, where
     *     the first element must be false in case of cache miss, and true otherwise
     *     the second element must be the actual value in case of success or null in case of cache miss.
     */
    abstract protected function fetch(string $key): array;

    protected function fetchMultiple(array $keys) {
        $res = [];
        foreach ($keys as $key) {
            [$found, $value] = $this->fetch($key);
            if ($found) {
                $res[$key] = $value;
            }
        }
        return $res;
    }

    /**
     * Puts data into the cache.
     *
     * @param int $lifeTime If 0 then infinite lifetime.
     * @return bool true if the entry was successfully stored in the cache, false otherwise.
     */
    abstract protected function save(string $key, $data, $lifeTime): bool;

    /**
     * Default implementation of doSaveMultiple. Each driver that supports multi-put should override it.
     *
     * @param array $keysAndValues Array of keys and values to save in cache
     * @param int $lifetime The lifetime. If != 0, sets a specific lifetime for these
     *                              cache entries (0 => infinite lifeTime).
     */
    protected function saveMultiple(array $keysAndValues, int $lifetime): bool {
        $success = true;
        foreach ($keysAndValues as $key => $value) {
            if (!$this->save($key, $value, $lifetime)) {
                $success = false;
            }
        }
        return $success;
    }

    private function dateIntervalToInt(\DateInterval $ttl): int {
        // Timestamp has 2038 year limitation, but it's unlikely to set TTL that long.
        return (new \DateTime())
            ->setTimestamp(0)
            ->add($ttl)
            ->getTimestamp();
    }
}
