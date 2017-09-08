<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Functional;

use Morpho\Network\Http\GeckoDriverDownloader;
use const Morpho\Web\PUBLIC_DIR_PATH;
use Morpho\Network\Http\SeleniumServerDownloader;
use Morpho\Network\Http\SeleniumServer;
use Morpho\Test\BrowserTestSuite;
use Morpho\Test\TestSettings;

class TestSuite extends BrowserTestSuite {
    public function testFilePaths(): iterable {
        return $this->testFilesInDir(__DIR__);
    }

    public function setUp() {
        parent::setUp();
        if (getenv('TRAVIS')) {
            $address = 'localhost:7654';
            $this->startPhpServer('http://' . $address);
        } else {
            $address = 'framework';
        }
        TestSettings::set('siteUri', 'http://' . $address);
    }

    protected function configureSeleniumServer(SeleniumServer $seleniumServer): void {
        $toolsDirPath = __DIR__;

        $geckoBinFilePath = (new GeckoDriverDownloader)($toolsDirPath . '/' . GeckoDriverDownloader::FILE_NAME);

        //$seleniumStandaloneFilePath = $toolsDirPath . '/selenium-server-standalone-3.4.0.jar';
        $seleniumStandaloneFilePath = (new SeleniumServerDownloader())($toolsDirPath);

        $seleniumServer->setServerJarFilePath($seleniumStandaloneFilePath)
            ->setGeckoBinFilePath($geckoBinFilePath)
            ->setLogFilePath($toolsDirPath . '/selenium.log')
            ->setPort(SeleniumServer::PORT);
    }

    private function startPhpServer(string $address): void {
        //showLn("Starting PHP server...");
        $cmd = 'php -S ' . escapeshellarg($address) . ' -t ' . escapeshellarg(PUBLIC_DIR_PATH) . ' &>/dev/null &';
        //cmd($cmd);
        proc_close(proc_open($cmd, [], $pipes));
        sleep(3); // @TODO: better way to check that the server is started
        //showLn("PHP server started");
    }
}
