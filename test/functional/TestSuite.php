<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Functional;

use Morpho\Infra\PhpServer;
use Morpho\Network\Address;
use Morpho\Network\Http\GeckoDriverDownloader;
use Morpho\Network\Http\SeleniumServerDownloader;
use Morpho\Network\Http\SeleniumServer;
use Morpho\Test\BrowserTestSuite;
use Morpho\Test\Sut;

class TestSuite extends BrowserTestSuite {
    private $phpServer;

    public function testFilePaths(): iterable {
        return $this->testFilesInDir(__DIR__);
    }

    public function setUp() {
        parent::setUp();
        //if (getenv('TRAVIS')) {
            $this->phpServer = $phpServer = new PhpServer(
                new Address('127.0.0.1', 7654),
                Sut::instance()->publicDirPath()
            );
            $address = $phpServer->start();
            /*
        } else {
            $address = 'framework';
        }
            */
        $this->sut()->settings()['siteUri'] = 'http://' . $address;
    }

    public function tearDown() {
        parent::tearDown();
        if ($this->phpServer) {
            $this->phpServer->stop();
        }
    }

    protected function configureSeleniumServer(SeleniumServer $seleniumServer): void {
        $toolsDirPath = __DIR__;

        $geckoBinFilePath = (new GeckoDriverDownloader)($toolsDirPath . '/' . GeckoDriverDownloader::FILE_NAME);

        //$seleniumStandaloneFilePath = $toolsDirPath . '/selenium-server-standalone-3.4.0.jar';
        $seleniumStandaloneFilePath = (new SeleniumServerDownloader())($toolsDirPath, getenv('SELENIUM_VERSION') ?: null);

        $seleniumServer->setServerJarFilePath($seleniumStandaloneFilePath)
            ->setGeckoBinFilePath($geckoBinFilePath)
            ->setLogFilePath($toolsDirPath . '/selenium.log')
            ->setPort(SeleniumServer::PORT);
    }
}
