<?php declare(strict_types=1);
namespace Morpho\Code\Compiler;

use Morpho\Base\IFn;

// Each compiler phase can contain other phases and therefore it is a recursive data type.
interface ICompilerPhase extends IFn {
}
