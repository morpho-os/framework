<?php
declare(strict_types=1);
namespace MorphoTest\Code\Peg;

use Morpho\Code\Peg\ParsingExpression;
use Morpho\Code\Peg\Peg;
use Morpho\Code\Peg\TerminalSymbol;
use Morpho\Test\TestCase;

class TerminalSymbolTest extends TestCase {
    public function testInheritance() {
        $this->assertInstanceOf(ParsingExpression::class, new TerminalSymbol('foo'));
    }

    // Examples from https://github.com/PhilippeSigaud/Pegged/wiki/PEG-Basics
    public function testLiteral() {
        $peg = new Peg();
        $this->assertEquals(new TerminalSymbol('('), $peg->match('(', '('));
        $this->assertEquals(new TerminalSymbol('abc'), $peg->match('abc', 'abc'));
        $this->assertEquals(new TerminalSymbol(' '), $peg->match(' ', ' '));
    }

    // @TODO: If the input characters are not those waited for, the expression fails and does not consume any input.
}