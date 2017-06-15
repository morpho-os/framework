<?php
declare(strict_types=1);

namespace MorphoTest\Code\Js;

use Morpho\Code\Js\DefineCallFixer;
use Morpho\Test\TestCase;

class DefineCallFixerTest extends TestCase {
    public function testFixDefineCall_AddsModuleIdIfItIsMissing() {
        $line = 'define(["require", "exports", "../../../system/lib/message"], function (require, exports, message_1) {';
        $this->assertEquals(
            'define("foo/some/list", ["require", "exports", "../../../system/lib/message"], function (require, exports, message_1) {',
            DefineCallFixer::fixDefineCall($line, __DIR__ . '/module', __DIR__ . '/module/foo/some/list.js')
        );
    }

    public function testFixDefineCall_IndexFileRules() {
        $line = 'define("widget", ["require", "exports", "event-manager"], function (require, exports, event_manager_1) {';
        $this->assertEquals(
            'define("system/lib/widget", ["require", "exports", "system/lib/event-manager"], function (require, exports, event_manager_1) {',
            DefineCallFixer::fixDefineCall($line, __DIR__ . '/module', __DIR__ . '/module/system/lib/widget.js')
        );
    }

    public function dataForFixDefineCall_RequireOrExportsNameAsModuleIdThrowsException() {
        return [
            [
                'require',
            ],
            [
                'exports',
            ],
        ];
    }

    /**
     * @dataProvider dataForFixDefineCall_RequireOrExportsNameAsModuleIdThrowsException
     */
    public function testFixDefineCall_RequireOrExportsNameAsModuleIdThrowsException($name) {
        $line = 'define("' . $name . '", [], function () {';
        $this->expectException(\UnexpectedValueException::class, "The 'require' or 'exports' names can't be used as module ID");
        DefineCallFixer::fixDefineCall($line, __DIR__, __DIR__ . '/foo');
    }
}