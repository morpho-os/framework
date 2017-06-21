<?php declare(strict_types=1);
namespace MorphoTest\Unit\Error;

use Morpho\Test\TestCase;
use Morpho\Error\WarningException;

class PhpErrorExceptionTest extends TestCase {
    public function testToString() {
        $e = new WarningException("My message", 0, E_WARNING);
        $expectedRegexp = "/^Morpho\\\\Error\\\\WarningException \(E_WARNING\): My message/si";
        $this->assertRegexp($expectedRegexp, $e->__toString());
    }
}
