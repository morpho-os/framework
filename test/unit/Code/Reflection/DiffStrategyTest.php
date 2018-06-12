<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Code\Reflection;

class DiffStrategyTest extends DiscoverStrategyTest {
    protected function mkDiscoverStrategy() {
        return new \Morpho\Code\Reflection\DiffStrategy();
    }
}
