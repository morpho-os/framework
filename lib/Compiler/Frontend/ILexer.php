<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler\Frontend;

use Morpho\Base\IFn;

// Lexexical analyzer/Scanner/Tokenizer
interface ILexer extends IFn {
    /**
     * Text => Stream<Token>
     */
    public function __invoke(mixed $context): mixed;
}
