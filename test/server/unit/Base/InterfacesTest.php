<?php declare(strict_types=1);
namespace MorphoTest\Base;

use Morpho\Base\IFn;
use Morpho\Test\TestCase;

class InterfacesTest extends TestCase {
    public function testIFn() {
        $obj = new class implements IFn {
            public $calledWith;
            public function __invoke(...$args) {
                $this->calledWith = $args;
            }
        };
        $obj('foo', 'bar', 'baz');
        $this->assertEquals(['foo', 'bar', 'baz'], $obj->calledWith);
    }
}