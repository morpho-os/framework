<?php declare(strict_types=1);
namespace MorphoTest\Unit\Base;

use Morpho\Base\ArrayIterator;
use Morpho\Test\TestCase;

class ArrayIteratorTest extends TestCase {
    private $it;

    public function setUp() {
        $this->it = new ArrayIterator();
    }

    public function testItem() {
        $this->it->append('foo');
        $this->assertEquals('foo', $this->it->item(0));
    }

    public function testClearAndIsEmpty() {
        $this->assertTrue($this->it->isEmpty());
        $this->assertEquals(0, $this->it->count());

        $this->it->append('abc');

        $this->assertFalse($this->it->isEmpty());
        $this->assertEquals(1, $this->it->count());

        $this->it->append(new \stdClass());

        $this->assertFalse($this->it->isEmpty());
        $this->assertEquals(2, $this->it->count());

        $this->it->clear();

        $this->assertEquals(0, $this->it->count());
        $this->assertTrue($this->it->isEmpty());
    }
}
