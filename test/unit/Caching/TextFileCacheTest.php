<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Caching;

use Morpho\Caching\TextFileCache;
use Morpho\Caching\ICache;

class TextFileCacheTest extends CacheTest {
    protected function newCache(): ICache {
        return new TextFileCache($this->createTmpDir());
    }
}