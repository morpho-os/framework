<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

class EmptyString extends ParsingExpression {
    public function parse($input) {
        return '';
    }
}