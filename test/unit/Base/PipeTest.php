<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use Morpho\Base\IFn;
use Morpho\Base\Pipe;
use Morpho\Testing\TestCase;

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

    public function testClosureAndIFnAsStages() {
        $val = null;
        $closure = function ($v) use (&$val)  {
            $val = $v;
            return $v;
        };
        $ifnImpl = new class implements IFn {
            public $val;
            public function __invoke($value) {
                $this->val = $value;
                return $value;
            }
        };
        $pipe = new Pipe([
            $ifnImpl,
            $closure,
        ]);

        $testVal = '123';

        $this->assertSame($testVal, $pipe->__invoke($testVal));

        $this->assertSame($testVal, $ifnImpl->val);
        $this->assertSame($testVal, $val);
    }

    public function testRunPreAndPostActionsForEachStage() {
        $pipe = new Pipe();
        $pipe->append(function ($value) use (&$stageArgs) {
            $stageArgs = \func_get_args();
            return ++$value;
        });
        $pipe->setBeforeEachAction(function ($value) use (&$beforeEachArgs) {
            $beforeEachArgs = \func_get_args();
            return ++$value;
        });
        $pipe->setAfterEachAction(function ($value) use (&$afterEachArgs) {
            $afterEachArgs = \func_get_args();
            return ++$value;
        });
        $this->assertSame(3, $pipe(0));
        $this->assertSame([0], $beforeEachArgs);
        $this->assertSame([1], $stageArgs);
        $this->assertSame([2], $afterEachArgs);
    }
}