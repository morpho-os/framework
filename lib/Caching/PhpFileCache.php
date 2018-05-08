<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;
use function Morpho\Base\requireFile;

/**
 * This class based on \Doctrine\Common\Cache\PhpFileCache from Doctrine project (MIT license).
 * For more information, see <http://www.doctrine-project.org>.
 * Copyright (c) 2006-2015 Doctrine Project
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class PhpFileCache extends FileCache {
    protected const EXTENSION = '.cache.php';

    public function __construct(string $dirPath, string $extension = null, int $umask = 0002) {
        parent::__construct($dirPath, $extension ?: self::EXTENSION, $umask);
    }

    public function has($key): bool {
        $value = $this->includeFile($key);
        if ($value === null) {
            return false;
        }
        return $value['lifetime'] === 0 || $value['lifetime'] > \time();
    }

    protected function fetch(string $key): array {
        $value = $this->includeFile($key);

        if ($value === null) {
            return [false, null];
        }

        if ($value['lifetime'] !== 0 && $value['lifetime'] < \time()) {
            return [false, null];
        }

        return [true, $value['data']];
    }

    protected function save(string $key, $data, $lifeTime = 0): bool {
        if ($lifeTime > 0) {
            $lifeTime = \time() + $lifeTime;
        }

        $cacheFilePath = $this->cacheFilePath($key);

        $value = [
            'lifetime' => $lifeTime,
            'data'     => $data,
        ];

        if (\is_object($data) && \method_exists($data, '__set_state')) {
            $value = \var_export($value, true);
            $code = \sprintf('<?php return %s;', $value);
        } else {
            $value = \var_export(\serialize($value), true);
            $code = \sprintf('<?php return unserialize(%s);', $value);
        }

        return $this->writeFile($cacheFilePath, $code);
    }

    private function includeFile(string $key): ?array {
        $filePath = $this->cacheFilePath($key);
        if (!\is_file($filePath)) {
            return null;
        }
        $value = requireFile($filePath);
        return isset($value['lifetime']) ? $value : null;
    }
}
