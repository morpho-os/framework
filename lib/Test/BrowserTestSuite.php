<?php
declare(strict_types = 1);
namespace Morpho\Test;
use Morpho\Inet\Http\SeleniumServer;

abstract class BrowserTestSuite extends TestSuite {
    private $server;

    public function setUp() {
        parent::setUp();
        $this->server = $this->startSeleniumServer();
    }

    public function tearDown() {
        parent::tearDown();
        if ($this->server) {
            $this->stopSeleniumServer($this->server);
        }
    }

    protected function startSeleniumServer(): SeleniumServer {
        return (new SeleniumServer())->start();
    }

    protected function stopSeleniumServer(SeleniumServer $server) {
        $server->stop();
    }
}