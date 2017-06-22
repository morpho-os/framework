<?php declare(strict_types=1);
namespace MorphoTest\Unit\Web\Session;

use Morpho\Test\DbTestCase;
use Morpho\Web\Session\DbSessionHandler;

class DbSessionHandlerTest extends DbTestCase {
    public function testInterface() {
        $handler = new DbSessionHandler();
        $this->assertInstanceOf('\\SessionHandlerInterface', $handler);
    }
}