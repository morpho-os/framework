<?php declare(strict_types=1);
namespace MorphoTest\Code\ClassTypeDiscoverer;

class DiffStrategyTest extends DiscoverStrategyTest {
    protected function createDiscoverStrategy() {
        return new \Morpho\Code\ClassTypeDiscoverer\DiffStrategy();
    }
}
