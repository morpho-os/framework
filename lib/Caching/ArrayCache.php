<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

/**
 * This class based on \Doctrine\Common\Cache\ArrayCache from Doctrine project
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 * Copyright (c) 2006-2015 Doctrine Project
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 * @author David Abdemoulaie <dave@hobodave.com>
 */
class ArrayCache extends Cache {
    /**
     * @var array[] $data each element being a tuple of [$data, $expiration], where the expiration is int|bool
     */
    private $data = [];

    /**
     * @var int
     */
    private $hitsCount = 0;

    /**
     * @var int
     */
    private $missesCount = 0;

    /**
     * @var int
     */
    private $upTime;

    public function __construct() {
        $this->upTime = time();
    }

    public function delete($key) {
        unset($this->data[$key]);
        return true;
    }

    public function stats(): ?array {
        return [
            Cache::STATS_HITS             => $this->hitsCount,
            Cache::STATS_MISSES           => $this->missesCount,
            Cache::STATS_UPTIME           => $this->upTime,
            Cache::STATS_MEMORY_USAGE     => null,
            Cache::STATS_MEMORY_AVAILABLE => null,
        ];
    }

    public function clear(): bool {
        $this->data = [];
        return true;
    }

    public function has($key): bool {
        if (!isset($this->data[$key])) {
            return false;
        }
        $expiration = $this->data[$key][1];
        if ($expiration && $expiration < time()) {
            $this->delete($key);
            return false;
        }
        return true;
    }

    protected function fetch(string $key): array {
        if (!isset($this->data[$key])) {
            $this->missesCount += 1;
            return [false, null];
        }
        $this->hitsCount += 1;
        return [true, $this->data[$key][0]];
    }

    protected function save(string $key, $data, $lifeTime = 0): bool {
        $this->data[$key] = [$data, $lifeTime ? time() + $lifeTime : false];
        return true;
    }
}
