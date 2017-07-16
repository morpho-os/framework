<?php
declare(strict_types=1);
namespace MorphoTest\Unit\Code\Js;

use Morpho\Code\Js\PJsCompiler;
use Morpho\Test\TestCase;

class PJsCompilerTest extends TestCase {
    public function testCompileFile() {
        $this->markTestIncomplete();
        // @TODO
        //$destFilePath = $this->createTmpDir() . '/dest.js';
    }

    public function testRuntimeFunctionCall() {
        $pjs = <<<OUT
<?php
\Morpho\Code\Js\alert("hello");
OUT;
        $expected = <<<OUT
"use strict";
alert("hello");
OUT;
        $this->checkOutput($expected, $pjs);
    }

    public function testEmptyClass() {
        $pjs = <<<OUT
<?php
class Animal {
}
OUT;
        $expected = <<<OUT
"use strict";
var Animal = (function () {
    function Animal() {
    }
    return Animal;
}());
OUT;
        $this->checkOutput($expected, $pjs);
    }

    public function testClass_InitializationPropertiesInConstructor() {
        $pjs = <<<'OUT'
<?php
class Animal {
    public $foo;
    protected $bar;
    protected $baz;
    
    public function __construct() {
        $this->foo = "foo";
        $this->bar = "bar";
        $this->baz = "baz";
    }
}
OUT;
        $expected = <<<'OUT'
"use strict";
var Animal = (function () {
    function Animal() {
        this.foo = "foo";
        this.bar = "bar";
        this.baz = "baz";
    }
    return Animal;
}());
OUT;
        $this->checkOutput($expected, $pjs);
    }

    public function testClass_NotInitializedProperties_NoConstructor() {
        $this->markTestIncomplete();
        $pjs = <<<'OUT'
<?php
class Animal {
    public $foo;
    protected $bar;
    protected $baz;
}
OUT;
        $expected = <<<'OUT'
"use strict";
var Animal = (function () {
    function Animal() {
        this.foo = undefined;
        this.bar = undefined;
        this.baz = undefined;
    }
    return Animal;
}());
OUT;
        $this->checkOutput($expected, $pjs);
    }
    
    public function testClass_InitializationOfPropertiesInPropertyDeclarations_NoConstructor() {
        $pjs = <<<'OUT'
<?php
class Animal {
    public $foo = 'foo';
    protected $bar = 'bar';
    protected $baz = 123;
}
OUT;
        $expected = <<<'OUT'
"use strict";
var Animal = (function () {
    function Animal() {
        this.foo = 'foo';
        this.bar = 'bar';
        this.baz = 123;
    }
    return Animal;
}());
OUT;
        $this->checkOutput($expected, $pjs);
    }

    public function testClass_InitializationOfPropertiesInPropertyDeclarations_WithConstructor() {
        $this->markTestIncomplete();
    }

    // @TODO: Test constructors' modifiers, params

    private function checkOutput(string $expected, string $pjs): void {
        $compiler = new PJsCompiler();
        $res = $compiler->newCompilation()
            ->append($pjs)
            ->run();
        $this->assertEquals($expected, $res[0]->output);
    }
}