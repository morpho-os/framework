<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\DataProcessing\Validation;

use Morpho\Test\TestCase;
use Morpho\DataProcessing\Validation\HostNameValidator;

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
