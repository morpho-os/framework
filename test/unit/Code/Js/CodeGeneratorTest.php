<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit\Code\Js;

use Morpho\Code\Js\CodeGenerator;
use Morpho\Test\TestCase;
use PhpParser\ParserFactory;

class CodeGeneratorTest extends TestCase {
    private $codeGen;

    public function setUp() {
        parent::setUp();
        $this->codeGen = new CodeGenerator();
    }
    
    public function testPrettyPrint_EmptyAst() {
        $this->assertSame('"use strict";', $this->codeGen->prettyPrintFile([]));
    }
    
    public function testPrettyPrint_FunctionCall() {
        $stmts = $this->parse(<<<OUT
<?php
\Morpho\Code\Js\alert("hello");
OUT
        );

        $this->assertSame("\"use strict\";\nalert(\"hello\");", $this->codeGen->prettyPrintFile($stmts));
    }

    private function parse(string $pjs): ?array {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        return $parser->parse($pjs);
    }
}