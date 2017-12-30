<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Js;

use Morpho\Base\IFn;

abstract class Compiler implements IFn {
    abstract public function __invoke($input): CompilationResult;
}