<?php declare(strict_types=1);
namespace MorphoTest\Unit\Base;

use Morpho\Base\ArrayObject;
use Morpho\Test\TestCase;

class ArrayObjectTest extends TestCase {
    public function setUp() {
        parent::setUp();
        $this->list = new ArrayObject();
    }

    public function testIsset() {
        $this->assertFalse(isset($list['foo']['bar']));
        $list['foo']['bar'] = 'baz';
        $this->assertTrue(isset($list['foo']['bar']));
    }

    public function testToArray() {
        $data = ['foo' => 'bar'];
        $this->list->exchangeArray($data);
        $this->assertEquals($data, $this->list->toArray());
    }
}
