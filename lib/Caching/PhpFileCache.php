<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

/**
 * This class based on \Doctrine\Common\Cache\PhpFileCache from Doctrine project
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 * Copyright (c) 2006-2015 Doctrine Project
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class PhpFileCache extends FileCache {
    const EXTENSION = '.doctrinecache.php';

    public function __construct($directory, $extension = self::EXTENSION, $umask = 0002) {
        parent::__construct($directory, $extension, $umask);
    }

    protected function fetch($key) {
        $value = $this->includeFileForId($key);

        if ($value === null) {
            return false;
        }

        if ($value['lifetime'] !== 0 && $value['lifetime'] < time()) {
            return false;
        }

        return $value['data'];
    }

    private function includeFileForId(string $key): ?array {
        $filePath = $this->getFilename($key);
        if (!is_file($filePath)) {
            return null;
        }
        $value = require $filePath;
        return $value['lifetime'] ?? null;
    }

    protected function contains($key) {
        $value = $this->includeFileForId($key);

        if ($value === null) {
            return false;
        }

        return $value['lifetime'] === 0 || $value['lifetime'] > time();
    }

    protected function save($key, $data, $lifeTime = 0) {
        if ($lifeTime > 0) {
            $lifeTime = time() + $lifeTime;
        }

        $filename = $this->getFilename($key);

        $value = [
            'lifetime' => $lifeTime,
            'data'     => $data,
        ];

        if (is_object($data) && method_exists($data, '__set_state')) {
            $value = var_export($value, true);
            $code = sprintf('<?php return %s;', $value);
        } else {
            $value = var_export(serialize($value), true);
            $code = sprintf('<?php return unserialize(%s);', $value);
        }

        return $this->writeFile($filename, $code);
    }
}
