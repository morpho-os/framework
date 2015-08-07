<?php
namespace MorphoTest\Pager;

use Morpho\Pager\Pager;
use Morpho\Test\TestCase;

class PagerTest extends TestCase {
    public function setUp() {
        $this->pager = new Pager();
    }

    public function testInterfaces() {
        $this->assertInstanceOf('\Iterator', $this->pager);
        $this->assertInstanceOf('\Countable', $this->pager);
    }

    public function testGetDefaultPageSize() {
        $this->assertEquals(20, $this->pager->getPageSize());
    }

    public function testPageSizeAccessors() {
        $this->pager->setPageSize(5);
        $this->assertEquals(5, $this->pager->getPageSize());
    }

    public function testPagesCount() {
        $this->assertEquals(0, count($this->pager));
        $this->assertEquals(0, $this->pager->count());
        $this->assertEquals(0, $this->pager->getTotalPagesCount());

        $this->pager->setItems([1, 2, 3, 4, 5, 6, 7]);
        $this->pager->setPageSize(2);

        $this->assertEquals(4, count($this->pager));
        $this->assertEquals(4, $this->pager->count());
        $this->assertEquals(4, $this->pager->getTotalPagesCount());

        $this->pager->setPageSize(20);
        $this->assertEquals(1, count($this->pager));
        $this->assertEquals(1, $this->pager->count());
        $this->assertEquals(1, $this->pager->getTotalPagesCount());

        $this->assertTrue(gettype($this->pager->count()) == 'integer');

        $this->pager->setItems([]);
        $this->assertEquals(0, $this->pager->getTotalPagesCount());

        $pager = new Pager();
        $pager->setItems([]);
        $pager->setCurrentPageNumber(2);
        $this->assertEquals(0, $pager->getTotalPagesCount());
    }

    public function testGetPage() {
        $totalItemsCount = 7;
        $this->pager->setPageSize(2);
        $items = range(0, $totalItemsCount - 1);
        $this->pager->setItems($items);
        $this->assertEquals(array(6), $this->pager->getPage(4)->toArray());
        $this->assertEquals(array(4, 5), $this->pager->getPage(3)->toArray());
        $this->assertEquals(array(0, 1), $this->pager->getPage(1)->toArray());

        // check bounds
        $this->assertEquals([], $this->pager->getPage(5)->toArray());
        $this->assertEquals([0, 1], $this->pager->getPage(-1)->toArray());
    }

    public function testIterator() {
        $items = array(1, 2, 3, 4, 5, 6, 7);
        $this->pager->setItems($items)
            ->setPageSize(2);

        $this->assertNull($this->pager->rewind());

        $this->assertTrue($this->pager->valid());
        $this->assertEquals(1, $this->pager->key());
        $this->assertInstanceOf('\Morpho\Pager\Page', $this->pager->current());
        $this->assertEquals(array(1, 2), $this->pager->current()->toArray());

        $this->assertNull($this->pager->next());

        $this->assertTrue($this->pager->valid());
        $this->assertEquals(2, $this->pager->key());
        $this->assertEquals(array(3, 4), $this->pager->current()->toArray());

        $this->assertNull($this->pager->next());

        $this->assertTrue($this->pager->valid());
        $this->assertEquals(3, $this->pager->key());
        $this->assertEquals(array(5, 6), $this->pager->current()->toArray());

        $this->assertNull($this->pager->next());

        $this->assertTrue($this->pager->valid());
        $this->assertEquals(4, $this->pager->key());
        $this->assertEquals(array(7), $this->pager->current()->toArray());

        $this->assertNull($this->pager->next());

        $this->assertFalse($this->pager->valid());
        $this->assertEquals(4, $this->pager->key());
        $this->assertEquals(array(7), $this->pager->current()->toArray());
    }
}
