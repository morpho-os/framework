<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

/**
 * This class based on \Doctrine\Common\Cache\FilesystemCache from Doctrine project
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 * Copyright (c) 2006-2015 Doctrine Project
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class FsCache extends FileCache {
    const EXTENSION = '.doctrinecache.data';

    public function __construct($directory, $extension = self::EXTENSION, $umask = 0002) {
        parent::__construct($directory, $extension, $umask);
    }

    protected function fetch($key) {
        $data = '';
        $lifetime = -1;
        $filename = $this->getFilename($key);

        if (!is_file($filename)) {
            return false;
        }

        $resource = fopen($filename, "r");

        if (false !== ($line = fgets($resource))) {
            $lifetime = (int)$line;
        }

        if ($lifetime !== 0 && $lifetime < time()) {
            fclose($resource);

            return false;
        }

        while (false !== ($line = fgets($resource))) {
            $data .= $line;
        }

        fclose($resource);

        return unserialize($data);
    }

    protected function contains($key) {
        $lifetime = -1;
        $filename = $this->getFilename($key);

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

    protected function save($key, $data, $lifeTime = 0) {
        if ($lifeTime > 0) {
            $lifeTime = time() + $lifeTime;
        }

        $data = serialize($data);
        $filename = $this->getFilename($key);

        return $this->writeFile($filename, $lifeTime . PHP_EOL . $data);
    }
}
