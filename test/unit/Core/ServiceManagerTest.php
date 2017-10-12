<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Core;

use Morpho\Test\TestCase;
use Morpho\Core\ServiceManager;

class ServiceManagerTest extends TestCase {
    public function testConfigAccessors() {
        $serviceManager = new class extends ServiceManager {
        };
        $this->assertSame([], $serviceManager->config());
        $newConfig = ['foo' => 'bar'];
        $this->assertNull($serviceManager->setConfig($newConfig));
        $this->assertSame($newConfig, $serviceManager->config());
    }
}