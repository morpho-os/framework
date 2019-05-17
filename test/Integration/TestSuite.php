<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Integration;

use Morpho\Infra\PhpServer;
use Morpho\Network\TcpAddress;
use Morpho\Network\Http\SeleniumServer;
use Morpho\Testing\BrowserTestSuite;
use Morpho\Testing\Sut;

class TestSuite extends BrowserTestSuite {
    /**
     * @var PhpServer
     */
    private static $phpServer;
    /**
     * @var SeleniumServer
     */
    private static $seleniumServer;

    protected $testCase = true; // to enable @before* and @after* annotations.

    public function testFilePaths(): iterable {
        return $this->testFilesInDir(__DIR__);
    }

    /**
     * @beforeClass
     * @after
     */
    public static function beforeAll() {
        self::$seleniumServer = SeleniumServer::mk([
            'geckoBinFilePath' => __DIR__ . '/geckodriver',
            'serverJarFilePath' => __DIR__ . '/selenium-server-standalone.jar',
            'serverVersion' => null,
            'logFilePath' => __DIR__ . '/selenium.log',
        ]);
        self::$seleniumServer->start();
        $sut = Sut::instance();
        if ($sut->config()['isTravis']) {
            self::$phpServer = $phpServer = new PhpServer(
                new TcpAddress($sut->config()['domain'], 7654),
                $sut->publicDirPath()
            );
            $address = $phpServer->start();
            $sut->config()['port'] = $address->port();
            $sut->config()['siteUri'] = 'http://' . $address;
        }
    }

    /**
     * @afterClass
     */
    public static function afterAll() {
        if (self::$phpServer) {
            self::$phpServer->stop();
        }
    }
}
