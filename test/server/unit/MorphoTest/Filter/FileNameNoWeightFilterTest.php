<?php
namespace MorphoTest\Filter;

use Morpho\Test\TestCase;
use Morpho\Filter\FileNameNoWeightFilter;

class FileNameNoWeightFilterTest extends TestCase {
    public function dataForFilter() {
        return array(
            array('/foo/bar/01.34_baz', '01.34_baz'),
            array('/foo/bar/01.34-baz', '01.34-baz'),
            array('/foo/bar/1_baz', '1_baz'),
            array('/foo/bar/1-baz', '1-baz'),
            array('/foo/bar/baz', 'baz'),
        );
    }

    /**
     * @dataProvider dataForFilter
     */
    public function testFilter($long, $short) {
        $filter = new FileNameNoWeightFilter();
        $this->assertEquals('/foo/bar/baz', $filter->filter($long));
        $this->assertEquals('baz', $filter->filter($short));
    }
}
