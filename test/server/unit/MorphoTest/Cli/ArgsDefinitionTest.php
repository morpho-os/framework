<?php
namespace MorphoTest\Cli;

use Morpho\Cli\ArgsDefinition;
use Morpho\Test\TestCase;

class ArgsDefinitionTest extends TestCase {
    public function testDefineShortArg_NotRepeated() {
        $argsDefinition = new ArgsDefinition();

        $argDefinition = $argsDefinition->shortArg('v');

        $this->assertEquals('v', $argDefinition->getName());
    }

}