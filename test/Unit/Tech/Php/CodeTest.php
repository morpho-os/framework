<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Php;

use Morpho\Tech\Php\Code;
use Morpho\Testing\TestCase;

class CodeTest extends TestCase {
    public function testVarToStr() {
        $this->assertSame(
            "['foo', 'bar']",
            Code::varToStr(['foo', 'bar'])
        );
        $this->assertSame(
            "[0 => 'foo', 1 => 'bar']",
            Code::varToStr(['foo', 'bar'], false)
        );
        $this->assertSame(
            "['foo' => 'bar']",
            Code::varToStr(['foo' => 'bar'])
        );
    }
}
