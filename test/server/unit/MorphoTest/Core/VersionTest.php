<?php
namespace MorphoTest\Core\VersionTest;

use Morpho\Test\TestCase;
use Morpho\Core\Version;

class VersionTest extends TestCase {
    public function testIsValid() {
        $this->assertTrue(Version::isValid('0'));
        $this->assertTrue(Version::isValid('0.0.0'));
        $this->assertTrue(Version::isValid('1'));
        $this->assertTrue(Version::isValid('0.1'));
        $this->assertTrue(Version::isValid('0.1.1'));
        $this->assertFalse(Version::isValid('0.1.1.1'));

        $this->assertFalse(Version::isValid('0.'));
        $this->assertFalse(Version::isValid('0.0.0.'));
        $this->assertFalse(Version::isValid('1.'));
        $this->assertFalse(Version::isValid('0.1.'));
        $this->assertFalse(Version::isValid('0.1.1.'));
        $this->assertFalse(Version::isValid('0.1.1.1.'));

        $this->assertFalse(Version::isValid('7.x-1.0-release1'));
        $this->assertTrue(Version::isValid('0.1-dev'));
        $this->assertFalse(Version::isValid('0.1-dev1'));
        $this->assertTrue(Version::isValid('0.1-alpha'));
        $this->assertTrue(Version::isValid('0.1-alpha1'));
        $this->assertTrue(Version::isValid('0.1-beta'));
        $this->assertTrue(Version::isValid('0.1-beta1'));
        $this->assertTrue(Version::isValid('0.1-rc'));
        $this->assertTrue(Version::isValid('0.1-rc1'));
    }

    public function testCurrentVersion() {
        $this->assertTrue(Version::isValid(Version::current()));
        $this->assertTrue(Version::isValid((string)(new Version())));
    }
}
