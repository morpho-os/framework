<?php
declare(strict_types = 1);
namespace Morpho\Test;
use Morpho\Network\Http\SeleniumServer;

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

    abstract protected function startSeleniumServer(): SeleniumServer;

    protected function stopSeleniumServer(SeleniumServer $server) {
        $server->stop();
    }
}