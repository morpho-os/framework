<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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
        $this->assertEquals('/foo/bar/baz', $filter->__invoke($long));
        $this->assertEquals('baz', $filter->__invoke($short));
    }
}
