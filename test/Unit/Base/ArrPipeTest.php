<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use Countable;
use Iterator;
use Morpho\Base\ArrPipe;
use Morpho\Base\IFn;
use Morpho\Testing\TestCase;

class ArrPipeTest extends TestCase {
    public function testInterface() {
        $pipe = new ArrPipe([]);
        $this->assertInstanceOf(IFn::class, $pipe);
        $this->assertInstanceOf(Iterator::class, $pipe);
        $this->assertInstanceOf(Countable::class, $pipe);
    }

    public function testInvoke_RunsAllPhases() {
        $phases = [
            function ($context) {
                $context['counter']++;
                return $context;
            },
            function ($context) {
                $context['counter']++;
                return $context;
            },
        ];
        $pipe = new ArrPipe($phases);
        $this->assertCount(2, $pipe->phases());
        $context = ['counter' => 0];

        $context = $pipe->__invoke($context);

        $this->assertSame(2, $context['counter']);
    }
}
