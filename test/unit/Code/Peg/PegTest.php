<?php
declare(strict_types=1);
namespace MorphoTest\Code\Peg;

use Morpho\Code\Peg\Alternation;
use Morpho\Code\Peg\Ast;
use Morpho\Code\Peg\EmptyString;
use Morpho\Code\Peg\NotPredicate;
use Morpho\Code\Peg\ParsingExpression;
use Morpho\Code\Peg\Peg;
use Morpho\Code\Peg\Sequence;
use Morpho\Code\Peg\TerminalSymbol;
use Morpho\Code\Peg\ZeroOrMoreRepetition;
use Morpho\Code\SyntaxError;
use Morpho\Test\TestCase;

class PegTest extends TestCase {
    public function testInheritanceForExpressions() {
        $expressions = [
            new EmptyString(),
            new TerminalSymbol('foo'),
            new Sequence(new TerminalSymbol('foo')),
            new Alternation(new TerminalSymbol('foo'), new TerminalSymbol('bar')),
            new ZeroOrMoreRepetition(new TerminalSymbol('foo')),
            new NotPredicate(new TerminalSymbol('foo')),
        ];
        foreach ($expressions as $expression) {
            $this->assertInstanceOf(ParsingExpression::class, $expression);
        }
    }

    /**
     * Tests below correspond to chapter "3.3 Interpretation of a Grammar" from http://bford.info/pub/lang/peg.pdf
     */

    public function testParse_EmptyString() {
        $peg = new Peg([
            'S' => new EmptyString()
        ]);
        $ast = $peg->parse('');
        $this->assertEquals(new Ast(['']), $ast);
    }
    
    public function testParse_Terminal_SuccessCase() {
        $peg = new Peg([
            'S' => new TerminalSymbol('a')
        ]);
        $ast = $peg->parse('afoo');
        $this->assertEquals(new Ast(['a']), $ast);
    }

    public function dataForParse_Terminal_FailureCase() {
        return [
            [
                '',
            ],
            [
                'bfoo',
            ],
        ];
    }

    /**
     * @dataProvider dataForParse_Terminal_FailureCase
     */
    public function testParse_Terminal_FailureCase(string $input) {
        $peg = new Peg([
            'S' => new TerminalSymbol('a')
        ]);
        $this->expectException(SyntaxError::class);
        $peg->parse($input);
    }

    public function testParse_Sequence_SuccessCase() {
        $peg = new Peg([
            'S' => new Sequence(new TerminalSymbol('abc'), new TerminalSymbol('def'))
        ]);
        $ast = $peg->parse('abcdefghi');
        $this->assertEquals(new Ast(['abcdef']), $ast);
    }

    public function dataForParse_Sequence_FailureCase() {
        return [
            [
                [new TerminalSymbol('abc'), new TerminalSymbol('abc')], 'abc',  // sequence longer then input
            ],
            [
                [new TerminalSymbol('efg'), $this->newRaiseErrorTerminalSymbol()], 'abc', // if e1 fails, then the all sequence fails without attempting e2 ($raiseErrorTerminal)
            ],
            [
                [new TerminalSymbol('abc'), new TerminalSymbol('xyz')], 'abcefg', // if e1 succeeds but e2 fails, then the sequence should fail.
            ],
        ];
    }

    /**
     * @dataProvider dataForParse_Sequence_FailureCase
     */
    public function testParse_Sequence_FailureCase(array $seq, string $input) {
        $peg = new Peg([
            'S' => new Sequence(...$seq)
        ]);
        $this->expectException(SyntaxError::class);
        $peg->parse($input);
    }

    public function dataForParse_Alternation() {
        return [
            [
                ['foo'], [new TerminalSymbol('foo'), $this->newRaiseErrorTerminalSymbol()], 'foobar', // if e1 succeeds, then e2 is not tested and result of the whole expression is result of e1.
            ],
            [
                ['foo'], [new TerminalSymbol('xyz'), new TerminalSymbol('foo')], 'foobar', // if e1 fails, then result of the whole expression is e2.
            ],
        ];
    }

    /**
     * @dataProvider dataForParse_Alternation
     */
    public function testParse_Alternation(array $expectedAst, array $alternation, string $input) {
        $peg = new Peg([
            'S' => new Alternation(...$alternation)
        ]);
        $ast = $peg->parse($input);
        $this->assertEquals(new Ast($expectedAst), $ast);
    }

    public function dataForZeroOrMoreRepetitions_RepetitionCase() {
        return [
            [
                ['foo'], 'foo', 'foobar',
            ],
            [
                ['foofoo'], 'foo', 'foofoobar',
            ],
        ];
    }

    /**
     * @dataProvider dataForZeroOrMoreRepetitions_RepetitionCase
     */
    public function testZeroOrMoreRepetitions_RepetitionCase(array $expectedAst, string $terminal, string $input) {
        $peg = new Peg([
            'S' => new ZeroOrMoreRepetition(new TerminalSymbol($terminal)),
        ]);
        $ast = $peg->parse($input);
        $this->assertEquals(new Ast($expectedAst), $ast);
    }

    public function testZeroOrMoreRepetitions_TerminationCase() {
        $peg = new Peg([
            'S' => new ZeroOrMoreRepetition(new TerminalSymbol('foo')),
        ]);
        $ast = $peg->parse('bar');
        $this->assertEquals(new Ast(['']), $ast);
    }
    
    public function testNotPredicate_Case1_InIsolation() {
        /*
if (e, xy) => (n, x)
then (!e, xy) => (n + 1, f)
If expression e succeeds consuming input x, then the syntactic predicate !e fails.
         */
        $input = 'foo';
        $this->assertFalse(
            (new NotPredicate(new TerminalSymbol($input)))->parse($input)
        );
    }

    public function testNotPredicate_Case1() {
        $input = 'foo';
        $peg = new Peg([
            'S' => new NotPredicate(new TerminalSymbol($input))
        ]);
        $this->expectException(SyntaxError::class);
        $peg->parse($input);
    }

    public function testNotPredicate_Case2_InIsolation() {
        /*
if (e, x) => (n, f)
then (!e, x) => (n + 1, E).
If e fails, then !e succeeds but consumes nothing.
        */
        $expression = new NotPredicate(new TerminalSymbol('foo'));
        $this->assertSame('', $expression->parse('bar'));
    }

    public function testNonPredicate_Case2() {
        $peg = new Peg([
            'S' => new NotPredicate(new TerminalSymbol('bar'))
        ]);
        $ast = $peg->parse('foo');
        $this->assertEquals(new Ast(['']), $ast);
    }

    /**
     * ε       : empty string
     * a       : terminal
     * A       : nonterminal
     * e1 e2   : sequence
     * e1 / e2 : prioritized choice/alternation
     * e*      : >= 0 repetitions
     * !e      : not predicate
     *
     * [a-z]   : character classes
     * .       : any character constant
     * ?       : option operator
     * +       : one-or-more repetitions operator
     * &       : and predicate operator
     */

    private function newRaiseErrorTerminalSymbol() {
        return new class extends TerminalSymbol {
            public function __construct() {
            }
            public function parse($input) {
                throw new \RuntimeException();
            }
        };
    }
}