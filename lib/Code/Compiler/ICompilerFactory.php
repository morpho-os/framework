<?php declare(strict_types=1);
namespace Morpho\Code\Compiler;

use Morpho\Code\Compiler\FrontEnd\IFrontEnd;
use Morpho\Code\Compiler\MiddleEnd\IMiddleEnd;
use Morpho\Code\Compiler\BackEnd\IBackEnd;

interface ICompilerFactory {
    public function setCompiler(ICompiler $compiler): void;

    public function mkFrontEnd(): IFrontEnd;

    public function mkMiddleEnd(): IMiddleEnd;

    public function mkBackEnd(): IBackEnd;
}
