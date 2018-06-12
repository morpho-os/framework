<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

use Morpho\App\App;
use Morpho\Testing\TestCase;

class AppTest extends TestCase {
    public function testConfigAccessors() {
        $app = new App();
        $this->assertEquals(new \ArrayObject([]), $app->config());

        $newConfig = new \ArrayObject(['foo' => 'bar']);
        $app = new App($newConfig);
        $this->assertSame($newConfig, $app->config());

        $newConfig = new \ArrayObject(['color' => 'orange']);
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($app->setConfig($newConfig));
        $this->assertSame($newConfig, $app->config());
    }
}
