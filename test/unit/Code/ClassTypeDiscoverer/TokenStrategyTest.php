<?php declare(strict_types=1);
namespace MorphoTest\Unit\Code\ClassTypeDiscoverer;

class TokenStrategyTest extends DiscoverStrategyTest {
    protected function newDiscoverStrategy() {
        return new \Morpho\Code\ClassTypeDiscoverer\TokenStrategy();
    }
}
