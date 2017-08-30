<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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