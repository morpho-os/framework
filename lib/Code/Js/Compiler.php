<?php
//declare(strict_types=1);
namespace Morpho\Code\Js;

use Morpho\Base\IFn;

abstract class Compiler implements IFn {
    abstract public function __invoke($input): CompilationResult;
}