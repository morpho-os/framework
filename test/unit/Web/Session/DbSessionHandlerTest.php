<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Web\Session;

use Morpho\Test\DbTestCase;
use Morpho\Web\Session\DbSessionHandler;

class DbSessionHandlerTest extends DbTestCase {
    public function testInterface() {
        $handler = new DbSessionHandler();
        $this->assertInstanceOf('\\SessionHandlerInterface', $handler);
    }
}