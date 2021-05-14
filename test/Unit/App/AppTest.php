<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App;

use Morpho\App\App;
use Morpho\Testing\TestCase;

class AppTest extends TestCase {
    public function testConfAccessors() {
        $app = new App();
        $this->assertEquals([], $app->conf());
        $newConf = ['foo' => 'bar'];
        $app = new App($newConf);
        $this->assertSame($newConf, $app->conf());
        $newConf = ['color' => 'orange'];
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($app->setConf($newConf));
        $this->assertSame($newConf, $app->conf());
    }
}