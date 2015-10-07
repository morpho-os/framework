<?php
namespace MorphoTest\Code;

use Morpho\Test\TestCase;
use Morpho\Code\CodeTool;

class CodeToolTest extends TestCase {
    public function testVarToPhp_ClosuresToFile() {
        $this->markTestIncomplete();
        $php = CodeTool::varToPhp(function () {
            echo "OK";
        });
        $this->assertEquals(<<<OUT
return function () {
    echo "OK";
};
OUT
            , $php
        );
    }
}