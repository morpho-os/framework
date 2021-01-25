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
use Morpho\Compiler\IBackEnd;
use Morpho\Compiler\ICompiler;
use Morpho\Compiler\ICompilerPhase;
use Morpho\Compiler\IFrontEnd;
use Morpho\Compiler\IInterpreter;
use Morpho\Compiler\IMiddleEnd;
use Morpho\Compiler\IProgram;
use Morpho\Compiler\ITranslationUnit;
use Morpho\Compiler\ITranslator;
use Morpho\Testing\TestCase;

class CompilerTest extends TestCase {
    public function testCompilerInterface() {
        $compiler = new Compiler();
        $this->assertInstanceOf(ITranslator::class, $compiler);
        $this->assertInstanceOf(ICompiler::class, $compiler);
        $this->assertInstanceOf(Pipe::class, $compiler);
        $this->assertInstanceOf(ICompilerPhase::class, new class implements IFrontEnd {
            public function __invoke($val) {
            }
        });
        $this->assertInstanceOf(ICompilerPhase::class, new class implements IMiddleEnd {
            public function __invoke($val) {
            }
        });
        $this->assertInstanceOf(ICompilerPhase::class, new class implements IBackEnd {
            public function __invoke($val) {
            }
        });
        $this->assertInstanceOf(ITranslator::class, new class implements IInterpreter {
            public function __invoke($val) {
            }
        });
        $this->assertInstanceOf(ITranslationUnit::class, new class implements IProgram {});
    }

    public function testCustomPhasesViaConstructorConf() {
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

        $conf = [
            'frontEnd' => $frontEnd,
            'middleEnd' => $middleEnd,
            'backEnd' => $backEnd,
        ];
        $compiler = new Compiler($conf);

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

    public function dataForPhasesAccessors() {
        yield [
            'frontEnd',
            'middleEnd',
            'backEnd',
        ];
    }

    /**
     * @dataProvider dataForPhasesAccessors
     */
    public function testPhasesAccessors(string $method) {
        $compiler = new Compiler();
        $oldPhase = $compiler->$method();
        $this->assertIsCallable($oldPhase);
        $newPhase = fn () => null;
        $this->assertSame($compiler, $compiler->{'set' . $method}($newPhase));
        $this->assertSame($newPhase, $compiler->$method());
        $this->assertNotSame($newPhase, $oldPhase);
    }

    public function testConfAccessors() {
        $compiler = new Compiler();
        $this->checkAccessors([$compiler, 'conf'], [], ['foo' => 'bar'], $compiler);
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