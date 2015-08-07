<?php
namespace MorphoTest\Core;

use Morpho\Test\TestCase;
use Morpho\Core\Module;

class ModuleTest extends TestCase {
    public function testIsEnabled() {
        $this->assertBoolAccessor([new MyModule, 'isEnabled'], false);
    }
}

class MyModule extends Module {
}
