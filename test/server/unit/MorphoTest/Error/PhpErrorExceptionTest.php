<?php
namespace MorphoTest\Error;

use Morpho\Test\TestCase;
use Morpho\Error\WarningException;

class PhpErrorExceptionTest extends TestCase {
    public function testToString() {
        $e = new WarningException("My message", 0, E_WARNING);
        $expectedRegexp = "/^exception 'Morpho\\\\Error\\\\WarningException' \(E_WARNING\) with message 'My message'/si";
        $this->assertRegexp($expectedRegexp, $e->__toString());
    }
}
