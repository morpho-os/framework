<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use Countable;
use Iterator;
use Morpho\Base\IFn;
use Morpho\Base\Pipe;
use Morpho\Testing\TestCase;

class PipeTest extends TestCase {
    public function testInterface() {
        $pipe = new class extends Pipe {
            public function current(): callable {
            }

            public function count(): int {
                return 0;
            }
        };
        $this->assertInstanceOf(IFn::class, $pipe);
        $this->assertInstanceOf(Iterator::class, $pipe);
        $this->assertInstanceOf(Countable::class, $pipe);
    }

    public function testIterator() {
        $phases = [
            0 => fn () => null,
            1 => fn () => null,
        ];
        $pipe = new class ($phases) extends Pipe {
            private array $phases;

            public function __construct($phases) {
                $this->phases = $phases;
            }

            public function current(): callable {
                return $this->phases[$this->index];
            }

            public function valid(): bool {
                return isset($this->phases[$this->index]);
            }

            public function count(): int {
                return count($this->phases);
            }
        };
        $i = 0;
        foreach ($pipe as $key => $val) {
            $this->assertSame($phases[$key], $val);
            $i++;
        }
        $this->assertCount(2, $pipe);
        $this->assertSame(2, $pipe->count());
        $this->assertSame(2, $i);
    }
}
