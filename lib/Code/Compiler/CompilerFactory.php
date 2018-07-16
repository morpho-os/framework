<?php declare(strict_types=1);
namespace Morpho\Code\Compiler;

use Morpho\Code\Compiler\BackEnd\BackEnd;
use Morpho\Code\Compiler\BackEnd\IBackEnd;
use Morpho\Code\Compiler\FrontEnd\FrontEnd;
use Morpho\Code\Compiler\FrontEnd\IFrontEnd;
use Morpho\Code\Compiler\MiddleEnd\IMiddleEnd;
use Morpho\Code\Compiler\MiddleEnd\MiddleEnd;

class CompilerFactory implements ICompilerFactory {
    private $compiler;

    public function setCompiler(ICompiler $compiler): void {
        $this->compiler = $compiler;
    }

    public function mkFrontEnd(): IFrontEnd {
        return new FrontEnd($this->compiler);
    }

    public function mkMiddleEnd(): IMiddleEnd {
        return new MiddleEnd($this->compiler);
    }

    public function mkBackEnd(): IBackEnd {
        return new BackEnd($this->compiler);
    }
}
