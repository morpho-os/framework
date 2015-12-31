<?php
namespace MorphoTest\Base;

use Morpho\Base\Object;
use Morpho\Test\TestCase;

class ObjectTest extends TestCase {
    public function setUp() {
        $this->object = new MyParent();
    }

    public function testGetClassFilePath() {
        $this->assertEquals(str_replace('\\', '/', __FILE__), $this->object->getClassFilePath());
    }

    public function testGetClassDirPath() {
        $this->assertEquals(str_replace('\\', '/', __DIR__), $this->object->getClassDirPath());
    }

    public function dataForToArray() {
        return [
            [new MyParent()],
            [new MyChild()],
        ];
    }

    /**
     * @dataProvider dataForToArray
     */
    public function testToArray($object) {
        $expected = [
            'publicProp'    => 'one',
            'protectedProp' => 'two',
        ];
        $this->assertEquals($expected, $object->toArray());
    }

    /**
     * We use the same dataProvider
     * @dataProvider dataForToArray
     */
    public function testFromArray($object) {
        $object->fromArray(['publicProp' => 'foo', 'protectedProp' => 'bar']);
        $this->assertEquals(['publicProp' => 'foo', 'protectedProp' => 'bar'], $object->toArray());
        $this->assertEquals('three', $object->getPrivateProp());
    }

    /**
     * We use the same dataProvider
     * @dataProvider dataForToArray
     */
    public function testSetPropertiesThroughConstructor($object) {
        $class = get_class($object);
        $object = new $class(['publicProp' => 'foo', 'protectedProp' => 'bar']);
        $this->assertEquals(['publicProp' => 'foo', 'protectedProp' => 'bar'], $object->toArray());
        $this->assertEquals('three', $object->getPrivateProp());
    }
}

class MyParent extends Object {
    public $publicProp = 'one';
    protected $protectedProp = 'two';
    private $privateProp = 'three';

    public function getPrivateProp() {
        return $this->privateProp;
    }
}

class MyChild extends MyParent {
}