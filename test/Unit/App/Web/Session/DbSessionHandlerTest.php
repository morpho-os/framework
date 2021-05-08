<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\Session;

use Morpho\App\Web\Session\DbSessionHandler;
use Morpho\Testing\DbTestCase;

class DbSessionHandlerTest extends DbTestCase {
    public function testInterface() {
        $handler = new DbSessionHandler();
        $this->assertInstanceOf('\\SessionHandlerInterface', $handler);
    }
}
