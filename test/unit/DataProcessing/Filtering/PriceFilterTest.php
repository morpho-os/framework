<?php declare(strict_types=1);
namespace MorphoTest\Unit\DataProcessing\Filtering;

use Morpho\Test\TestCase;
use Morpho\DataProcessing\Filtering\PriceFilter;

class PriceFilterTest extends TestCase {
    public function setUp() {
        $this->filter = new PriceFilter();
    }

    public function testReturnsNullIfNotPossibleToFilter() {
        $this->assertNull($this->filter->filter('abc'));
    }

    public function testReturnsNullForNonScalar() {
        $this->assertNull($this->filter->filter([]));
    }

    public function testCanFilterMixedValue() {
        $this->assertEquals(
            '3.1415',
            $this->filter->filter(
                "ab3 , c1 f\n4fa^1**5z"
            )
        );
    }

    public function testMultipleDotsAndCommas() {
        $this->assertEquals(14.12, $this->filter->filter('14,..,...12'));
        $this->assertNull($this->filter->filter('1..,4,..,...12'));
    }

    public function testNegativeValue() {
        $value = -0.001;
        $this->assertEquals($value, $this->filter->filter($value));
    }
}
