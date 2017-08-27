<?php
declare(strict_types=1);
namespace MorphoTest\Code\Peg;

use Morpho\Code\Peg\Ast;
use Morpho\Code\Peg\EmptyString;
use Morpho\Code\Peg\Peg;
use Morpho\Test\TestCase;

class PegTest extends TestCase {
    public function testParse_EmptyString() {
        $peg = new Peg([
            'S' => [new EmptyString()]
        ]);
        $this->assertEquals(
            new Ast(['']),
            $peg->parse('')
        );
    }
}