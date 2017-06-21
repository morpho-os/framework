<?php declare(strict_types=1);
namespace MorphoTest\Unit\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\Compiler;

class CompilerTest extends TestCase {
    public function testAppendSourceInfo() {
        $this->checkBoolAccessor([new Compiler, 'appendSourceInfo'], true);
    }
}

