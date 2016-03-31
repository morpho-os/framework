<?php
namespace MorphoTest\Code;

use Morpho\Test\TestCase;
use Morpho\Code\ClassDiscoverer;

class ClassDiscovererTest extends TestCase {
    public function setUp() {
        $this->classDiscoverer = new ClassDiscoverer();
    }

    public function testGetClassMapForDir_UsingDefaultStrategy() {
        $this->assertEquals(str_replace('\\', '/', __FILE__), $this->classDiscoverer->getClassMapForDir(__DIR__)[__CLASS__]);
    }

    public function testGetDefaultStrategy() {
        $this->assertInstanceOf('\Morpho\Code\ClassDiscoverer\TokenStrategy', $this->classDiscoverer->getDiscoverStrategy());
    }

    public function testGetClassMapForDir_UsingCustomStrategy() {
        $discoverStrategy = $this->getMock('\Morpho\Code\ClassDiscoverer\IDiscoverStrategy');
        $discoverStrategy->expects($this->atLeastOnce())
            ->method('getClassesForFile')
            ->will($this->returnValue([]));
        $this->assertInstanceOf(get_class($this->classDiscoverer), $this->classDiscoverer->setDiscoverStrategy($discoverStrategy));
        $this->classDiscoverer->getClassMapForDir(__DIR__);
    }
}
