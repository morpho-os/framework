<?php
namespace MorphoTest\Base;

use Morpho\Base\Object;
use Morpho\Test\TestCase;

class ObjectTest extends TestCase {
    public function setUp() {
        $this->object = new MyObject();
    }

    public function testGetClassFilePath() {
        $this->assertEquals(str_replace('\\', '/', __FILE__), $this->object->getClassFilePath());
    }

    public function testGetClassDirPath() {
        $this->assertEquals(str_replace('\\', '/', __DIR__), $this->object->getClassDirPath());
    }
}

class MyObject extends Object {
}