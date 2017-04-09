<?php
namespace MorphoTest\Base;

use Morpho\Base\Object;
use Morpho\Test\TestCase;

class ObjectTest extends TestCase {
    public function setUp() {
        $this->object = new MyObject();
    }

    public function testClassFilePath() {
        $this->assertEquals(str_replace('\\', '/', __FILE__), $this->object->classFilePath());
    }

    public function testClassDirPath() {
        $this->assertEquals(str_replace('\\', '/', __DIR__), $this->object->classDirPath());
    }
}

class MyObject extends Object {
}