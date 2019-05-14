<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\DataProcessing\Filtering;

use Morpho\Testing\TestCase;
use Morpho\DataProcessing\Filtering\PriceFilter;

class PriceFilterTest extends TestCase {
    /**
     * @var \Morpho\Base\IFn
     */
    private $filter;

    public function setUp(): void {
        parent::setUp();
        $this->filter = new PriceFilter();
    }

    public function testReturnsNullIfNotPossibleToFilter() {
        $this->assertNull($this->filter->__invoke('abc'));
    }

    public function testReturnsNullForNonScalar() {
        $this->assertNull($this->filter->__invoke([]));
    }

    public function testCanFilterMixedValue() {
        $this->assertEquals(
            '3.1415',
            $this->filter->__invoke(
                "ab3 , c1 f\n4fa^1**5z"
            )
        );
    }

    public function testMultipleDotsAndCommas() {
        $this->assertEquals(14.12, $this->filter->__invoke('14,..,...12'));
        $this->assertNull($this->filter->__invoke('1..,4,..,...12'));
    }

    public function testNegativeValue() {
        $value = -0.001;
        $this->assertEquals($value, $this->filter->__invoke($value));
    }
}
