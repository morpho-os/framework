<?php
declare(strict_types=1);

namespace MorphoTest\Code\Js;

use Morpho\Code\Js\CompilationResult;
use Morpho\Code\Js\PJsCompiler;
use Morpho\Test\TestCase;

class PJsCompilerTest extends TestCase {
    public function testHelloWorld() {
        $compiler = new PJsCompiler();
        $destFilePath = $this->createTmpDir() . '/dest.js';
        $res = $compiler->compile()
            ->inFilePath($this->getTestDirPath() . '/hello-world.pjs')
            ->outFilePath($destFilePath)
            ->run();
        $this->assertInstanceOf(CompilationResult::class, $res);
        //d($res);
    }
}