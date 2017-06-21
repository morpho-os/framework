<?php declare(strict_types=1);
namespace MorphoTest\Unit\DataProcessing\Filtering;

use Morpho\Test\TestCase;
use Morpho\DataProcessing\Filtering\FileNameNoWeightFilter;

class FileNameNoWeightFilterTest extends TestCase {
    public function dataForFilter() {
        return [
            ['/foo/bar/01.34_baz', '01.34_baz'],
            ['/foo/bar/01.34-baz', '01.34-baz'],
            ['/foo/bar/1_baz', '1_baz'],
            ['/foo/bar/1-baz', '1-baz'],
            ['/foo/bar/baz', 'baz'],
        ];
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
