<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Compiler;

use ArrayObject;
use Morpho\Base\Pipe;
use Morpho\Compiler\Backend\IBackend;
use Morpho\Compiler\Backend\IInterpreter;
use Morpho\Compiler\Compiler;
use Morpho\Compiler\ICompiler;
use Morpho\Compiler\ICompilerPhase;
use Morpho\Compiler\IMidend;
use Morpho\Compiler\ITranslator;

class CompilerTest extends ConfigurablePipeTest {
    public function testCompilerInterface() {
        $compiler = new Compiler($this->mkCompilerConf());
        $this->assertInstanceOf(ITranslator::class, $compiler);
        $this->assertInstanceOf(ICompiler::class, $compiler);
        $this->assertInstanceOf(Pipe::class, $compiler);
        $this->assertInstanceOf(
            ICompilerPhase::class,
            new class implements IMidend {
                public function __invoke(mixed $val): mixed {
                }
            }
        );
        $this->assertInstanceOf(
            ICompilerPhase::class,
            new class implements IBackend {
                public function __invoke(mixed $val): mixed {
                }
            }
        );
        $this->assertInstanceOf(
            ITranslator::class,
            new class implements IInterpreter {
                public function __invoke(mixed $val): mixed {
                }
            }
        );
    }

    public function testCustomPhasesViaConstructorConf() {
        $frontend = function ($v) {
            $v['frontend'] = 'frontend ok';
            return $v;
        };
        $midend = function ($v) {
            $v['midend'] = 'midend ok';
            return $v;
        };
        $backend = function ($v) {
            $v['backend'] = 'backend ok';
            $v['target'] = $v['source'];
            return $v;
        };

        $conf = [
            'frontend' => $frontend,
            'midend'   => $midend,
            'backend'  => $backend,
        ];
        $compiler = new Compiler($conf);

        $this->assertSame($frontend, $compiler->frontend());
        $this->assertSame($midend, $compiler->midend());
        $this->assertSame($backend, $compiler->backend());

        $source = '';
        $context = new ArrayObject(
            [
                'source' => $source,
            ]
        );

        $result = $compiler($context);

        $this->assertSame($result, $context);
        $this->assertSame($source, $result['source']);
        $this->assertSame($source, $result['target']); // should not be changed
        $this->assertSame('frontend ok', $context['frontend']);
        $this->assertSame('midend ok', $context['midend']);
        $this->assertSame('backend ok', $context['backend']);
    }

    public function dataPhasesAccessors() {
        yield [
            'frontend',
            'midend',
            'backend',
        ];
    }

    /**
     * @dataProvider dataPhasesAccessors
     */
    public function testPhasesAccessors(string $method) {
        $compiler = new Compiler($this->mkCompilerConf());
        $oldPhase = $compiler->$method();
        $this->assertIsCallable($oldPhase);
        $newPhase = fn() => null;
        $this->assertSame($compiler, $compiler->{'set' . $method}($newPhase));
        $this->assertSame($newPhase, $compiler->$method());
        $this->assertNotSame($newPhase, $oldPhase);
    }

    public function testDefaultPhases() {
        $compiler = new Compiler($this->mkCompilerConf());

        $frontend = $compiler->frontend();
        $this->assertIsCallable($frontend);

        $midend = $compiler->midend();
        $this->assertIsCallable($midend);
        $this->assertNotSame($frontend, $midend);

        $backend = $compiler->backend();
        $this->assertIsCallable($backend);
        $this->assertNotSame($frontend, $backend);
        $this->assertNotSame($midend, $backend);

        $context['source'] = '';
        $this->assertSame($context, $compiler($context));
    }

    private function mkCompilerConf(): array {
        return [
            'frontend' => fn($v) => $v,
            'midend'   => fn($v) => $v,
            'backend'  => fn($v) => $v,
        ];
    }
}