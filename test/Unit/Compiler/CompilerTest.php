<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Compiler;

use Morpho\Compiler\CompiledLangFactory;
use Morpho\Compiler\InterpretedLangFactory;
use Morpho\Testing\TestCase;
use Morpho\Compiler\Compiler;
use Morpho\Compiler\IComponentFactory;
use Morpho\Base\IFn;

class CompilerTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(IFn::class, new Compiler([]));
    }

    public function testShouldAllowDoNothing() {
        $context = ['foo' => 'bar'];
        $this->assertSame($context['foo'], (new Compiler())->__invoke($context)['foo']);
    }

    public function testCustomComponents() {
        $conf = [];
        $conf['factory'] = new class implements IComponentFactory {
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
    
    public function testCompiledLangPhases() {
        $conf = [
            'factory' => new class extends CompiledLangFactory {

            },
        ];
        $compiler = new Compiler($conf);

        $result = $compiler->__invoke();



    }
    
    public function testInterpretedLangPhases() {
        $conf = [
            'factory' => new class extends InterpretedLangFactory {

            },
        ];
        $compiler = new Compiler($conf);
        $result = $compiler->__invoke();

    }
}