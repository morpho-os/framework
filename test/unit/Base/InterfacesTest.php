<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use Morpho\Base\IFn;
use Morpho\Test\TestCase;

class InterfacesTest extends TestCase {
    public function testIFn() {
        $obj = new class implements IFn {
            public $calledWith;
            public function __invoke($value) {
                $this->calledWith = func_get_args();
            }
        };
        $obj(['foo', 'bar', 'baz']);
        $this->assertEquals([['foo', 'bar', 'baz']], $obj->calledWith);
    }
}