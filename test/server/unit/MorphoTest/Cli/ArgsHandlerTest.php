<?php
namespace MorphoTest\Cli;

use Morpho\Test\TestCase;
use Morpho\Cli\ArgsHandler;

class ArgsHandlerTest extends TestCase {
    public function testArgValuesAccessor() {
        $argsHandler = new ArgsHandler();
        $args = ['foo', '--bar', 'val'];
        $argsHandler->setArgValues($args);
        $this->assertEquals($args, $argsHandler->getArgValues());
    }

    public function testShortArg_ThrowsExceptionWhenNonBoolArgDefined() {
        $argsHandler = new ArgsHandler();

        $argsDefinition = $argsHandler->define();

        $argsDefinition->shortArg('o');
    }
    /*
        public function testUsage_BoolArgs() {

            $argsHandler->setArgValues(['--foo', '--bar', '-baz', 'hh', '--pizza', 'foo=bar', '-v=a', '-v=b', '-v=c', '-o', '-o']);

            $argsDefinition = $argsHandler->define();

            $argsDefinition->longBoolArg('foo');
            $argsDefinition->longBoolArg('bar');
            $argsDefinition->shortBoolArg('baz');
            $argsDefinition->longBoolArg('pizza');
            $argsDefinition->shortArg('v');


            $this->assertFalse($argsHandler->hasLongArg('some'));
            $this->assertFalse($argsHandler->hasShortArg('some'));

            $this->assertTrue($argsHandler->hasLongArg('foo'));
            $this->assertFalse($argsHandler->hasShortArg('foo'));

            $this->assertTrue($argsHandler->hasLongArg('bar'));
            $this->assertFalse($argsHandler->hasShortArg('bar'));

            $this->assertTrue($argsHandler->hasShortArg('baz'));
            $this->assertFalse($argsHandler->hasLongArg('baz'));

            $this->assertTrue($argsHandler->hasLongArg('pizza'));
            $this->assertFalse($argsHandler->hasShortArg('pizza'));

            $this->assertEquals(['hh'], $argsHandler->getValueArgs());
        }
    */
}