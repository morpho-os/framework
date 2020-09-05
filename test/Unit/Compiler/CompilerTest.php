<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Compiler;

use Morpho\Testing\TestCase;
use Morpho\Compiler\Compiler;
use Morpho\Compiler\IFactory;
use Morpho\Base\IFn;

class CompilerTest extends TestCase {
    public function testIsFn() {
        $this->assertInstanceOf(IFn::class, new Compiler([]));
    }

    public function testCompilation() {
        $conf = [];
        $conf['factory'] = new class implements IFactory { 
            public function mkFrontEnd(): callable {
                return function ($context) {
                    $context['frontEnd'] = 'front-end run';
                    return $context;
                };
            }

            public function mkBackEnd(): callable {
                return function ($context) {
                    $context['backEnd'] = 'back-end run';
                    return $context;
                };
            }

            public function mkMiddleEnd(): callable {
                return function ($context) {
                    $context['middleEnd'] = 'middle-end run';
                    return $context;
                };
            }
        };
        $compiler = new Compiler($conf);
        $context = [];
        $context = $compiler($context);
        $this->assertCount(4, $context);
        $this->assertSame('front-end run', $context['frontEnd']);
        $this->assertSame('middle-end run', $context['middleEnd']);
        $this->assertSame('back-end run', $context['backEnd']);
        $this->assertSame($compiler, $context['compiler']);
    }
}
