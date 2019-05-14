<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Network\Http;

use Morpho\Network\Http\SeleniumServer;
use Morpho\Testing\TestCase;

class SeleniumServerTest extends TestCase {
    private $seleniumServer;

    public function setUp(): void {
        parent::setUp();
        $this->seleniumServer = new SeleniumServer('/tmp/foo/bar');
    }

    public function testServerJarFilePathAccessors() {
        $this->checkAccessors([$this->seleniumServer, 'serverJarFilePath'], $this->checkNotEmpty(), '/foo/bar/baz');
    }

    public function testLogFilePathAccessors() {
        $this->checkAccessors([$this->seleniumServer, 'logFilePath'], null, '/foo/bar/baz');
    }

    public function testPortAccessors() {
        $this->checkAccessors([$this->seleniumServer, 'port'], SeleniumServer::PORT, 1234);
    }

    public function testGeckoBinFilePathAccessors() {
        $this->checkAccessors([$this->seleniumServer, 'geckoBinFilePath'], $this->checkNotEmpty(), '/foo/bar/baz');
    }

    private function checkNotEmpty() {
        return function ($initialValue) {
            $this->assertNotEmpty($initialValue);
        };
    }
}
