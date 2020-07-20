<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Base;

use Morpho\Testing\TestCase;
use Morpho\Base\Env;

class EnvTest extends TestCase {
    private $oldZendEnableGc;

    public function setUp(): void {
        parent::setUp();
        $this->oldZendEnableGc = \ini_set('zend.enable_gc', '1'); // we change this setting below.
    }

    public function tearDown(): void {
        parent::tearDown();
        \ini_set('zend.enable_gc', $this->oldZendEnableGc);
    }

    public function testIsCli() {
        $this->assertTrue(Env::isCli());
    }

    public function testBoolIniVal() {
        $this->assertTrue(Env::boolIniVal('realpath_cache_size'));

        $setting = 'zend.enable_gc';
        $this->assertTrue(Env::boolIniVal($setting));

        \ini_set($setting, '0');
        $this->assertFalse(Env::boolIniVal($setting));

        \ini_set($setting, '1');
        $this->assertTrue(Env::boolIniVal($setting));

        // Names are case sensitive, so such setting should not exist.
        $this->assertFalse(Env::boolIniVal(\strtoupper($setting)));

        $this->assertFalse(Env::boolIniVal(__FUNCTION__));
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
        $this->assertEquals($expected, Env::iniValToBool($actual));
    }

    public function testIsBoolLikeIniVal() {
        $this->assertFalse(Env::isBoolLikeIniVal('abc'));
        $this->assertFalse(Env::isBoolLikeIniVal('100M'));
        $this->assertFalse(Env::isBoolLikeIniVal('01'));
        $this->assertFalse(Env::isBoolLikeIniVal('10'));
        $this->assertFalse(Env::isBoolLikeIniVal(10));
        $this->assertFalse(Env::isBoolLikeIniVal('2'));
        $this->assertFalse(Env::isBoolLikeIniVal('-1'));
        $this->assertFalse(Env::isBoolLikeIniVal(-1));
        $this->assertFalse(Env::isBoolLikeIniVal(2));
        $this->assertFalse(Env::isBoolLikeIniVal('90.58333'));
        $this->assertFalse(Env::isBoolLikeIniVal(90.58333));
        $this->assertFalse(Env::isBoolLikeIniVal('&'));
        foreach (['on', 'true', 'yes', '1', 1, 'off', 'false', 'none', '', '0', 0] as $v) {
            $this->assertTrue(Env::isBoolLikeIniVal($v));
        }
    }

    public function testTmpDirPath() {
        $tmpDirPath = Env::tmpDirPath();
        $this->assertNotEmpty($tmpDirPath && (false === \strpos($tmpDirPath, '\\')));
    }
}