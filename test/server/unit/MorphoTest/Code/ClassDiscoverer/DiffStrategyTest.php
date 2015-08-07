<?php
namespace MorphoTest\Code\ClassDiscoverer;

class DiffStrategyTest extends DiscoverStrategyTest {
    protected function createDiscoverStrategy() {
        return new \Morpho\Code\ClassDiscoverer\DiffStrategy();
    }
}
