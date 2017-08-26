<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class Peg {
    public function match(string $grammar, string $input): ParsingExpression {
        if ($grammar === $input) {
            return new TerminalSymbol($input);
        }
        throw new \UnexpectedValueException();
    }
}