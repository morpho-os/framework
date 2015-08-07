<?php
namespace MorphoTest\Base;

use Morpho\Test\TestCase;
use Morpho\Base\Environment;

class EnvironmentTest extends TestCase {
    public function testIsCli() {
        $this->assertTrue(Environment::isCli());
    }
}
