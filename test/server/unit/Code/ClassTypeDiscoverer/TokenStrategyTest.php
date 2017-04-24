<?php declare(strict_types=1);
namespace MorphoTest\Code\ClassTypeDiscoverer;

class TokenStrategyTest extends DiscoverStrategyTest {
    protected function createDiscoverStrategy() {
        return new \Morpho\Code\ClassTypeDiscoverer\TokenStrategy();
    }
}
