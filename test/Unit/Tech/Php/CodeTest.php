<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Php;

use Morpho\Testing\TestCase;
use Morpho\Tech\Php\Code;

class CodeTest extends TestCase {
    public function testVarToStr_ClosuresToFile() {
        $this->markTestIncomplete();
        $php = Code::varToStr(function () {
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
