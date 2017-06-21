<?php declare(strict_types=1);
namespace MorphoTest\Unit\Code;

use Morpho\Test\TestCase;
use Morpho\Code\CodeTool;

class CodeToolTest extends TestCase {
    public function testVarToString_ClosuresToFile() {
        $this->markTestIncomplete();
        $php = CodeTool::varToString(function () {
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