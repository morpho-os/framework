<?php
namespace MorphoTest\Base;

use Morpho\Test\TestCase;
use Morpho\Base\Converter;

class ConverterTest extends TestCase {
    public function testToBytes() {
        $this->assertEquals(10 * pow(2, 20), Converter::toBytes('10M'));
    }
}
