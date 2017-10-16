<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\ModuleConfig;
use Morpho\Web\ModulePathManager;

class ModuleConfigTest extends TestCase {
    public function testInterface() {
        $this->assertInstanceOf(\ArrayAccess::class, new ModuleConfig($this->createMock(ModulePathManager::class), 'foo/bar', []));
    }

    public function testArrayAccessAndDefaultUsageOfSiteConfig() {
        $pathManager = $this->createMock(ModulePathManager::class);
        $moduleName = 'book/page';
        $siteConfig = [
            'modules' => [
                $moduleName => [
                    'bar' => 'test',
                ],
            ],
        ];
        $config = new class ($pathManager, $moduleName, $siteConfig) extends ModuleConfig {
            protected function init(): void {
                if (null === $this->config) {
                    $this->config = [];
                }
            }
        };
        $config['foo'] = 123;
        $this->assertSame(123, $config['foo']);
        $this->assertSame('test', $config['bar']);
        unset($config['bar']);
        $this->assertFalse(isset($config['bar']));
    }

    public function testConfigFileNotExists() {
        $config = new ModuleConfig($this->createMock(ModulePathManager::class), 'foo/bar', []);
        $this->assertFalse(isset($config['test']));
    }
}