<?php
declare(strict_types=1);
namespace MorphoTest\Unit\Base;

use Morpho\Base\IFn;
use Morpho\Base\Pipe;
use Morpho\Test\TestCase;

class PipeTest extends TestCase {
    public function testInterface() {
        $pipe = new Pipe();
        $this->assertInstanceOf(\ArrayObject::class, $pipe);
        $this->assertInstanceOf(IFn::class, $pipe);
    }

    public function testOverloadIteratorAggregate() {
        $pipe = new class extends Pipe {
            public function getIterator() {
                return new \ArrayIterator(['foo', 'bar', 'baz']);
            }
        };
        $this->assertEquals(['foo', 'bar', 'baz'], iterator_to_array($pipe));
    }

    public function testAppendFluentInterface() {
        $pipe = new Pipe();
        $pipe->append('foo')
            ->append('bar');
        $this->assertEquals(['foo', 'bar'], iterator_to_array($pipe));
    }
}