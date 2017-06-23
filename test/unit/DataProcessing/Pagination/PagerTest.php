<?php declare(strict_types=1);
namespace MorphoTest\Unit\DataProcessing\Pagination;

use Countable;
use Morpho\DataProcessing\Pagination\Page;
use Morpho\DataProcessing\Pagination\Pager;
use Morpho\Test\TestCase;

class PagerTest extends TestCase {
    private $pager;

    public function setUp() {
        $this->pager = new Pager('foo');
    }

    public function testInterface() {
        $this->assertInstanceOf(\Traversable::class, $this->pager);
        $this->assertInstanceOf(Countable::class, $this->pager);
    }

    public function testDefaultPageSize() {
        $this->assertEquals(20, $this->pager->pageSize());
    }

    public function testPageSizeAccessors() {
        $this->pager->setPageSize(5);
        $this->assertEquals(5, $this->pager->pageSize());
    }

    public function testPagesCount() {
        $this->assertEquals(0, count($this->pager));
        $this->assertEquals(0, $this->pager->count());
        $this->assertEquals(0, $this->pager->totalPagesCount());

        $this->pager->setItems([1, 2, 3, 4, 5, 6, 7]);
        $this->pager->setPageSize(2);

        $this->assertEquals(4, count($this->pager));
        $this->assertEquals(4, $this->pager->count());
        $this->assertEquals(4, $this->pager->totalPagesCount());

        $this->pager->setPageSize(20);
        $this->assertEquals(1, count($this->pager));
        $this->assertEquals(1, $this->pager->count());
        $this->assertEquals(1, $this->pager->totalPagesCount());

        $this->assertTrue(gettype($this->pager->count()) == 'integer');

        $this->pager->setItems([]);
        $this->assertEquals(0, $this->pager->totalPagesCount());

        $pager = new Pager('foo');
        $pager->setItems([]);
        $pager->setCurrentPageNumber(2);
        $this->assertEquals(0, $pager->totalPagesCount());
    }

    public function testPage() {
        $totalItemsCount = 7;
        $this->pager->setPageSize(2);
        $items = range(0, $totalItemsCount - 1);
        $this->pager->setItems($items);
        $this->assertEquals([6], $this->pager->page(4)->toArray());
        $this->assertEquals([4, 5], $this->pager->page(3)->toArray());
        $this->assertEquals([0, 1], $this->pager->page(1)->toArray());

        // check bounds
        $this->assertEquals([], $this->pager->page(5)->toArray());
        $this->assertEquals([0, 1], $this->pager->page(-1)->toArray());
    }

    public function testIterator() {
        $items = [1, 2, 3, 4, 5, 6, 7];
        $this->pager->setItems($items)
            ->setPageSize(2);

        $this->assertNull($this->pager->rewind());

        $this->assertTrue($this->pager->valid());
        $this->assertEquals(1, $this->pager->key());
        $this->assertInstanceOf(Page::class, $this->pager->current());
        $this->assertEquals([1, 2], $this->pager->current()->toArray());

        $this->assertNull($this->pager->next());

        $this->assertTrue($this->pager->valid());
        $this->assertEquals(2, $this->pager->key());
        $this->assertEquals([3, 4], $this->pager->current()->toArray());

        $this->assertNull($this->pager->next());

        $this->assertTrue($this->pager->valid());
        $this->assertEquals(3, $this->pager->key());
        $this->assertEquals([5, 6], $this->pager->current()->toArray());

        $this->assertNull($this->pager->next());

        $this->assertTrue($this->pager->valid());
        $this->assertEquals(4, $this->pager->key());
        $this->assertEquals([7], $this->pager->current()->toArray());

        $this->assertNull($this->pager->next());

        $this->assertFalse($this->pager->valid());
        $this->assertEquals(4, $this->pager->key());
        $this->assertEquals([7], $this->pager->current()->toArray());
    }
}
