<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

/**
 * This class based on https://github.com/Roave/DoctrineSimpleCache/blob/master/src/SimpleCacheAdapter.php
 * The MIT License (MIT)
 * Copyright (c) 2017 Roave, LLC.
 */
class SimpleCacheAdapter implements ICache {
    private $doctrineCache;

    public function __construct(DoctrineCache $doctrineCache) {
        $this->doctrineCache = $doctrineCache;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null) {

        $value = $this->doctrineCache->fetch($key);
        if ($value === false) {
            // Doctrine cache returns `false` when cache doesn't contain, but also `false` if the value stored is
            // `false`, so check to see if the cache contains the key; if so, we probably meant to return `false`
            if ($this->doctrineCache->contains($key)) {
                return false;
            }
            return $default;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null): bool {

        if ($ttl === null) {
            return $this->doctrineCache->save($key, $value);
        }

        if ($ttl instanceof \DateInterval) {
            $ttl = $this->convertDateIntervalToInteger($ttl);
        }

        if (!is_int($ttl)) {
            throw InvalidArgumentException::fromKeyAndInvalidTTL($key, $ttl);
        }

        if ($ttl <= 0) {
            return $this->delete($key);
        }

        return $this->doctrineCache->save($key, $value, $ttl);
    }

    private function convertDateIntervalToInteger(\DateInterval $ttl): int {
        // Timestamp has 2038 year limitation, but it's unlikely to set TTL that long.
        return (new \DateTime())
            ->setTimestamp(0)
            ->add($ttl)
            ->getTimestamp();
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key): bool {
        return $this->doctrineCache->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool {
        return $this->doctrineCache->deleteAll();
    }

    /**
     * @param array|\Traversable $keys
     * @param mixed $default
     * @return array
     * @throws \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
     */
    public function getMultiple($keys, $default = null): array {
        $keys = $this->filterValidateMultipleKeys($keys);
        return array_merge(array_fill_keys($keys, $default), $this->doctrineCache->fetchMultiple($keys));
    }

    /**
     * @param mixed $keys
     * @return array
     * @throws \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
     */
    private function filterValidateMultipleKeys($keys): array {
        if ($keys instanceof \Traversable) {
            $keys = iterator_to_array($keys);
        }

        if (!is_array($keys)) {
            throw Exception\InvalidArgumentException::fromNonIterableKeys($keys);
        }

        array_map([$this, 'validateKey'], $keys);

        return $keys;
    }

    /**
     * @param array|\Traversable $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null): bool {
        if (!$values instanceof \Traversable && !is_array($values)) {
            throw Exception\InvalidArgumentException::fromNonIterableKeys($values);
        }

        $validatedValues = [];
        foreach ($values as $k => $v) {
            $this->validateKey($k);
            $validatedValues[$k] = $v;
        }

        if ($ttl === null) {
            return $this->doctrineCache->saveMultiple($validatedValues);
        }

        if ($ttl instanceof \DateInterval) {
            $ttl = $this->convertDateIntervalToInteger($ttl);
        }

        if (!is_int($ttl)) {
            throw InvalidArgumentException::fromKeyAndInvalidTTL(key($validatedValues), $ttl);
        }

        if ($ttl <= 0) {
            return $this->deleteMultiple(array_keys($validatedValues));
        }

        return $this->doctrineCache->saveMultiple($validatedValues, $ttl);
    }

    /**
     * @param array|\Traversable $keys
     * @return bool
     * @throws \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
     */
    public function deleteMultiple($keys): bool {
        return $this->doctrineCache->deleteMultiple($this->filterValidateMultipleKeys($keys));
    }

    /**
     * {@inheritDoc}
     */
    public function has($key): bool {
        $this->validateKey($key);
        return $this->doctrineCache->contains($key);
    }
}
