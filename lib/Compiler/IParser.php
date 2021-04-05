<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler;

use Morpho\Base\IFn;

// Aka SyntaxAnalyzer
interface IParser extends IFn {
    /**
     * Stream<Token> => IProgram
     * @param $context
     * @return IProgram
     */
    public function __invoke($context): IProgram;
}
