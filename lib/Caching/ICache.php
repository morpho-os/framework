<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Caching;

/**
 * Some ideas for this interface taken from \Doctrine\Common\Cache\CacheProvider from Doctrine project (http://www.doctrine-project.org)
 *
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-16-simple-cache.md
 */
interface ICache extends \Psr\SimpleCache\CacheInterface {
    /**
     * Returns statistic information about cache
     */
    public function stats(): ?array;
}
