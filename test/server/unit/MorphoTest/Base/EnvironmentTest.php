<?php
namespace MorphoTest\Base;

use Morpho\Test\TestCase;
use Morpho\Base\Environment;

class EnvironmentTest extends TestCase {
    public function setUp() {
        parent::setUp();
        $this->oldZendEnableGc = ini_set('zend.enable_gc', 1);
    }

    public function tearDown() {
        parent::tearDown();
        ini_set('zend.enable_gc', $this->oldZendEnableGc);
    }

    public function testIsCli() {
        $this->assertTrue(Environment::isCli());
    }

    public function testGetBoolIniVal() {
        $this->assertTrue(Environment::getBoolIniVal('realpath_cache_size'));

        $setting = 'zend.enable_gc';
        $this->assertTrue(Environment::getBoolIniVal($setting));

        ini_set($setting, 0);
        $this->assertFalse(Environment::getBoolIniVal($setting));

        ini_set($setting, 1);
        $this->assertTrue(Environment::getBoolIniVal($setting));

        // Names are case sensitive, so such setting should not exist.
        $this->assertFalse(Environment::getBoolIniVal(strtoupper($setting)));

        $this->assertFalse(Environment::getBoolIniVal(__FUNCTION__));
    }

    public function dataForIniValToBool() {
        return [
            [
                true,
                '123',
            ],
            [
                true,
                'some',
            ],
            [
                true,
                'On',
            ],
            [
                true,
                '1',
            ],
            [
                true,
                1,
            ],
            [
                true,
                'True',
            ],
            [
                true,
                'true',
            ],
            [
                true,
                true,
            ],
            [
                true,
                'yes',
            ],
            [
                true,
                -1,
            ],
            [
                false,
                0,
            ],
            [
                false,
                '0',
            ],
            [
                false,
                'Off',
            ],
            [
                false,
                'False',
            ],
            [
                false,
                'false',
            ],
            [
                false,
                'No',
            ],
            [
                false,
                'None',
            ],
            [
                false,
                '',
            ],
        ];
    }

    /**
     * @dataProvider dataForIniValToBool
     */
    public function testIniValToBool($expected, $actual) {
        $this->assertEquals($expected, Environment::iniValToBool($actual));
    }

    public function testIsBoolLikeIniVal() {
        $this->assertFalse(Environment::isBoolLikeIniVal('abc'));
        $this->assertFalse(Environment::isBoolLikeIniVal('100M'));
        $this->assertFalse(Environment::isBoolLikeIniVal('01'));
        $this->assertFalse(Environment::isBoolLikeIniVal('10'));
        $this->assertFalse(Environment::isBoolLikeIniVal(10));
        $this->assertFalse(Environment::isBoolLikeIniVal('2'));
        $this->assertFalse(Environment::isBoolLikeIniVal('-1'));
        $this->assertFalse(Environment::isBoolLikeIniVal(-1));
        $this->assertFalse(Environment::isBoolLikeIniVal(2));
        $this->assertFalse(Environment::isBoolLikeIniVal('90.58333'));
        $this->assertFalse(Environment::isBoolLikeIniVal(90.58333));
        $this->assertFalse(Environment::isBoolLikeIniVal('&'));
        foreach (['on', 'true', 'yes', '1', 1, 'off', 'false', 'none', '', '0', 0] as $v) {
            $this->assertTrue(Environment::isBoolLikeIniVal($v));
        }
    }
}
