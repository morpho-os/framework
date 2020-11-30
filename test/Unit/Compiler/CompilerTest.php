<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Compiler;

use ArrayObject;
use Morpho\Base\Pipe;
use Morpho\Compiler\Compiler;
use Morpho\Testing\TestCase;

class CompilerTest extends TestCase {
    public function testCustomPhases() {
        $frontEnd = function ($v) {
            $v['frontEnd'] = 'front-end ok';
            return $v;
        };
        $middleEnd = function ($v) {
            $v['middleEnd'] = 'middle-end ok';
            return $v;
        };
        $backEnd = function ($v) {
            $v['backEnd'] = 'back-end ok';
            $v['target'] = $v['source'];
            return $v;
        };
        $compiler = new Compiler([
            'frontEnd' => $frontEnd,
            'middleEnd' => $middleEnd,
            'backEnd' => $backEnd,
        ]);

        $this->assertInstanceOf(Pipe::class, $compiler);

        $this->assertSame($frontEnd, $compiler->frontEnd());
        $this->assertSame($middleEnd, $compiler->middleEnd());
        $this->assertSame($backEnd, $compiler->backEnd());

        $source = '';
        $context = new ArrayObject([
            'source' => $source,
        ]);

        $result = $compiler($context);

        $this->assertSame($result, $context);
        $this->assertSame($source, $result['source']);
        $this->assertSame($source, $result['target']); // should not be changed
        $this->assertSame('front-end ok', $context['frontEnd']);
        $this->assertSame('middle-end ok', $context['middleEnd']);
        $this->assertSame('back-end ok', $context['backEnd']);
    }

    public function testDefaultPhases() {
        $compiler = new Compiler();

        $frontEnd = $compiler->frontEnd();
        $this->assertIsCallable($frontEnd);

        $middleEnd = $compiler->middleEnd();
        $this->assertIsCallable($middleEnd);
        $this->assertNotSame($frontEnd, $middleEnd);

        $backEnd = $compiler->backEnd();
        $this->assertIsCallable($backEnd);
        $this->assertNotSame($frontEnd, $backEnd);
        $this->assertNotSame($middleEnd, $backEnd);

        $context['source'] = '';
        $this->assertSame($context, $compiler($context));
    }
}