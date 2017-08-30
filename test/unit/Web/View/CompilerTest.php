<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\Compiler;

class CompilerTest extends TestCase {
    public function testAppendSourceInfo() {
        $this->checkBoolAccessor([new Compiler, 'appendSourceInfo'], true);
    }
}

