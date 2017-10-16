<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\SiteConfig;
use Morpho\Web\SitePathManager;

class SiteConfigTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(\ArrayAccess::class, new SiteConfig($this->createMock(SitePathManager::class)));
    }

    public function testArrayAccess() {
        $pathManager = $this->createConfiguredMock(SitePathManager::class, [
            'configFilePath' => $this->getTestDirPath() . '/' . SitePathManager::CONFIG_FILE_NAME
        ]);
        $config = new SiteConfig($pathManager);
        $this->assertTrue(isset($config['foo']));
        $this->assertSame(123, $config['foo']);
        $this->assertFalse(isset($config['bar']));
        unset($config['foo']);
        $this->assertFalse(isset($config['foo']));

        $this->assertFalse(isset($config['baz']));
        $config['baz'] = 'test';
        $this->assertTrue(isset($config['baz']));
        $this->assertSame('test', $config['baz']);
    }
}