<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App;

interface IModuleIndexer {
    /**
     * Indexes all modules and returns the index. Can cache the result.
     * @return array|\ArrayAccess
     */
    public function index();

    /**
     * Clears the internal state and cache so that the next call of the index() will build a new index.
     */
    public function clear(): void;
}
