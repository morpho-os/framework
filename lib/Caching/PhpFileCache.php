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

    /**
     * @var callable
     *
     * This is cached in a local static variable to avoid instantiating a closure each time we need an empty handler
     */
    private static $emptyErrorHandler;

    /**
     * {@inheritdoc}
     */
    public function __construct($directory, $extension = self::EXTENSION, $umask = 0002) {
        parent::__construct($directory, $extension, $umask);

        self::$emptyErrorHandler = function () {
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id) {
        $value = $this->includeFileForId($id);

        if ($value === null) {
            return false;
        }

        if ($value['lifetime'] !== 0 && $value['lifetime'] < time()) {
            return false;
        }

        return $value['data'];
    }

    /**
     * @param string $id
     *
     * @return array|null
     */
    private function includeFileForId(string $id): ?array {
        $fileName = $this->getFilename($id);

        // note: error suppression is still faster than `file_exists`, `is_file` and `is_readable`
        set_error_handler(self::$emptyErrorHandler);

        $value = include $fileName;

        restore_error_handler();

        if (!isset($value['lifetime'])) {
            return null;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id) {
        $value = $this->includeFileForId($id);

        if ($value === null) {
            return false;
        }

        return $value['lifetime'] === 0 || $value['lifetime'] > time();
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0) {
        if ($lifeTime > 0) {
            $lifeTime = time() + $lifeTime;
        }

        $filename = $this->getFilename($id);

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
