<?php
namespace MorphoTest\Code\ClassDiscoverer;

class RegexpStrategyTest extends DiscoverStrategyTest {
    protected function createDiscoverStrategy() {
        return new \Morpho\Code\ClassDiscoverer\RegexpStrategy();
    }
}
