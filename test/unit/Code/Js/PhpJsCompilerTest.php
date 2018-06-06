<?php declare(strict_types=1);
namespace Morpho\Test\Unit\Code\Js;

use Morpho\Code\Js\PhpJsCompiler;
use Morpho\Testing\TestCase;

class PhpJsCompilerTest extends TestCase {
    public function testInvoke_EmptyProgram() {
        $compiler = new PhpJsCompiler();
        $sourceProgram = '';
        $this->assertSame('', $compiler->__invoke($sourceProgram));
    }

    public function testInvoke_IncludeExpr() {
        $compiler = new PhpJsCompiler();
        $sourceProgram = '<?php require "foo/bar.php";';
        $this->assertSame('', $compiler->__invoke($sourceProgram));
    }
}
