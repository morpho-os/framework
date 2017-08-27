<?php
//declare(strict_types=1);
namespace Morpho\Code\Peg;

abstract class ParsingExpression {
    abstract public function parse($input);
}