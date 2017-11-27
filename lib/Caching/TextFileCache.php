<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

/**
 * This class based on \Doctrine\Common\Cache\FilesystemCache from Doctrine project (MIT license).
 * For more information, see <http://www.doctrine-project.org>.
 * Copyright (c) 2006-2015 Doctrine Project
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class TextFileCache extends FileCache {
    protected const EXTENSION = '.cache';

    public function __construct(string $dirPath, string $extension = null, int $umask = 0002) {
        parent::__construct($dirPath, $extension ?: self::EXTENSION, $umask);
    }

    public function has($key): bool {
        $lifetime = -1;
        $filename = $this->cacheFilePath($key);
        if (!is_file($filename)) {
            return false;
        }
        $resource = fopen($filename, "r");
        if (false !== ($line = fgets($resource))) {
            $lifetime = (int)$line;
        }
        fclose($resource);
        return $lifetime === 0 || $lifetime > time();
    }

    protected function fetch(string $key): array {
        $data = '';
        $lifetime = -1;
        $filename = $this->cacheFilePath($key);
        if (!is_file($filename)) {
            return [false, null];
        }
        $resource = fopen($filename, "r");
        if (false !== ($line = fgets($resource))) {
            $lifetime = (int)$line;
        }
        if ($lifetime !== 0 && $lifetime < time()) {
            fclose($resource);
            return [false, null];
        }
        while (false !== ($line = fgets($resource))) {
            $data .= $line;
        }
        fclose($resource);
        return [true, unserialize($data)];
    }

    protected function save(string $key, $data, $lifeTime = 0): bool {
        if ($lifeTime > 0) {
            $lifeTime = time() + $lifeTime;
        }
        $data = serialize($data);
        $cacheFilePath = $this->cacheFilePath($key);
        return $this->writeFile($cacheFilePath, $lifeTime . PHP_EOL . $data);
    }
}