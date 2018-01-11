<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Base;

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
