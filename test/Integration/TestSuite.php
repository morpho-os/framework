<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Integration;

use Morpho\Network\PhpServer;
use Morpho\Network\TcpAddress;
use Morpho\Testing\BrowserTestSuite;
use Morpho\Testing\Sut;

class TestSuite extends BrowserTestSuite {
    /**
     * @var PhpServer
     */
    private static $phpServer;

    protected $testCase = true; // to enable @before* and @after* annotations.

    public function testFilePaths(): iterable {
        return $this->testFilesInDir(__DIR__);
    }

    /**
     * @beforeClass
     * @after
     */
    public static function beforeAll(): void {
        $sut = Sut::instance();

        BrowserTestSuite::startSeleniumServer($sut);

        if ($sut['isTravis']) {
            $sut['phpServer'] = $phpServer = new PhpServer(
                new TcpAddress($sut['domain'], 7654),
                $sut['publicDirPath']
            );
            $address = $phpServer->start();
            $sut['port'] = $address->port();
            $sut['uri'] = 'http://' . $address;
        }
    }

    /**
     * @afterClass
     */
    public static function afterAll(): void {
        if (self::$phpServer) {
            self::$phpServer->stop();
        }

        BrowserTestSuite::stopSeleniumServer(Sut::instance());
    }
}