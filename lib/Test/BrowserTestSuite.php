<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types = 1);
namespace Morpho\Test;

use Morpho\Network\Http\SeleniumServer;

abstract class BrowserTestSuite extends TestSuite {
    private $server;

    public function setUp(): void {
        parent::setUp();
        $server = new SeleniumServer();
        $this->configureSeleniumServer($server);
        $server->start();
        $this->server = $server;
    }

    public function tearDown(): void {
        parent::tearDown();
        if ($this->server) {
            $this->server->stop();
        }
    }

    protected function configureSeleniumServer(SeleniumServer $seleniumServer): void {
        // Do nothing here, should be overridden in child classes.
    }
}