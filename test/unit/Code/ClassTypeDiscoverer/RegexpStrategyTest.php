<?php declare(strict_types=1);
namespace MorphoTest\Code\ClassTypeDiscoverer;

class RegexpStrategyTest extends DiscoverStrategyTest {
    protected function newDiscoverStrategy() {
        return new \Morpho\Code\ClassTypeDiscoverer\RegexpStrategy();
    }
}