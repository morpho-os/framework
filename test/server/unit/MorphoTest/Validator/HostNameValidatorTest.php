<?php
namespace MorphoTest\Validator;

use Morpho\Test\TestCase;
use Morpho\Validator\HostNameValidator;

class HostNameValidatorTest extends TestCase {
    public function setUp() {
        $this->validator = new HostNameValidator();
    }

    public function testIsValidForLocalhost() {
        $this->assertTrue($this->validator->isValid('localhost'));
        $this->assertTrue($this->validator->isValid('127.0.0.1'));
    }

    public function testIsValidForEmptyHost() {
        $this->assertFalse($this->validator->isValid(''));
    }
}
