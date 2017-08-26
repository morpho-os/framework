<?php
declare(strict_types=1);
namespace MorphoTest\Unit\Network\Http;

use Morpho\Network\Http\SeleniumServer;
use Morpho\Test\TestCase;

class SeleniumServerTest extends TestCase {
    private $seleniumServer;

    public function setUp() {
        $this->seleniumServer = new SeleniumServer('/tmp/foo/bar');
    }

    public function testServerJarFilePathAccessors() {
        $this->checkAccessors($this->seleniumServer, $this->checkNotEmpty(), '/foo/bar/baz', 'serverJarFilePath');
    }

    public function testLogFilePathAccessors() {
        $this->checkAccessors($this->seleniumServer, null, '/foo/bar/baz', 'logFilePath');
    }

    public function testPortAccessors() {
        $this->checkAccessors($this->seleniumServer, SeleniumServer::PORT, 1234, 'port');
    }

    public function testGeckoBinFilePathAccessors() {
        $this->checkAccessors($this->seleniumServer, $this->checkNotEmpty(), '/foo/bar/baz', 'geckoBinFilePath');
    }

    private function checkNotEmpty() {
        return function ($initialValue) {
            $this->assertNotEmpty($initialValue);
        };
    }
}