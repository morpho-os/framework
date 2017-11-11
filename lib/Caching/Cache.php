<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

use Doctrine\Common\Cache\PhpFileCache;
use function Morpho\Base\dasherize;
use Psr\SimpleCache\CacheInterface as ICache;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;

class Cache {
    public static function normalizeKey(string $key): string {
        return md5(dasherize($key));
    }

    public static function newFileCache(string $cacheDirPath): ICache {
        return new SimpleCacheAdapter(new PhpFileCache($cacheDirPath));
    }
}