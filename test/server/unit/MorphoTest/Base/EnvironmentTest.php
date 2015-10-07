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

    public function testIsIniSet() {
        $this->assertTrue(Environment::isIniSet('realpath_cache_size'));

        $setting = 'zend.enable_gc';
        $this->assertTrue(Environment::isIniSet($setting));

        ini_set($setting, 0);
        $this->assertFalse(Environment::isIniSet($setting));

        ini_set($setting, 1);
        $this->assertTrue(Environment::isIniSet($setting));

        // Names are case sensitive, so such setting should not exist.
        $this->assertFalse(Environment::isIniSet(strtoupper($setting)));

        $this->assertFalse(Environment::isIniSet(__FUNCTION__));
    }

    public function dataForIniToBool() {
        return [
            [
                true,
                'None',
            ],
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
        ];
    }

    /**
     * @dataProvider dataForIniToBool
     */
    public function testIniToBool($expected, $actual) {
        $this->assertEquals($expected, Environment::iniToBool($actual));
    }
}
