<?php
namespace MorphoTest\Code;

use Morpho\Test\TestCase;
use Morpho\Code\CodeTool;

class CodeToolTest extends TestCase {
    public function testVarToPhp_ClosuresToFile() {
        $this->markTestIncomplete();
        $var = function () {
            echo "OK";
        };
        $tmpFilePath = $this->createTmpDir(__FUNCTION__) . '/foo';
        $php = CodeTool::varToPhp($var, $tmpFilePath);
        $this->assertEquals(<<<OUT
return function () {
    echo "OK";
};
OUT
            , $php
        );
    }
}