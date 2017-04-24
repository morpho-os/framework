<?php declare(strict_types=1);
namespace MorphoTest\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\Compiler;

class CompilerTest extends TestCase {
    public function testAppendSourceInfo() {
        $this->assertBoolAccessor([new Compiler, 'appendSourceInfo'], true);
    }
}

