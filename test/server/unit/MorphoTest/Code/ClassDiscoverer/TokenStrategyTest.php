<?php
namespace MorphoTest\Code\ClassDiscoverer;

class TokenStrategyTest extends DiscoverStrategyTest {
    protected function createDiscoverStrategy() {
        return new \Morpho\Code\ClassDiscoverer\TokenStrategy();
    }
}
