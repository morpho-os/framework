<?php declare(strict_types=1);
namespace MorphoTest\Functional;

use function Morpho\Base\fromJson;
use function Morpho\Base\showLn;
use function Morpho\Cli\cmd;
use Morpho\Inet\Http\SeleniumServerDownloader;
use const Morpho\Web\PUBLIC_DIR_PATH;
use Morpho\Inet\Http\HttpClient;
use Morpho\Inet\Http\SeleniumServer;
use Morpho\Test\BrowserTestSuite;
use Morpho\Test\TestSettings;

class TestSuite extends BrowserTestSuite {
    public function testFilePaths(): iterable {
        return $this->testFilesInDir(__DIR__);
    }

    public function setUp() {
        parent::setUp();
        if (getenv('TRAVIS')) {
            showLn("Starting PHP server...");
            $address = 'localhost:7654';
            $cmd = 'php -S ' . escapeshellarg($address) . ' -t ' . escapeshellarg(PUBLIC_DIR_PATH) . ' &>/dev/null &';
            //cmd($cmd);
            proc_close(proc_open($cmd, [], $pipes));
            sleep(3); // @TODO: better way to check that the server is started
            TestSettings::set('siteUri', 'http://' . $address);
            showLn("PHP server started");
        } else {
            TestSettings::set('siteUri', 'http://framework');
        }
    }

    protected function startSeleniumServer(): SeleniumServer {
        $toolsDirPath = __DIR__;
        $seleniumStandaloneFilePath = (new SeleniumServerDownloader())($toolsDirPath);
        //$seleniumStandaloneFilePath = $toolsDirPath . '/selenium-server-standalone-3.4.0.jar';
        $geckoBinFilePath = $this->downloadGeckoDriver($toolsDirPath);
        return (new SeleniumServer())
            ->setGeckoBinFilePath($geckoBinFilePath)
            ->setLogFilePath(__DIR__ . '/selenium.log')
            ->setServerJarFilePath($seleniumStandaloneFilePath)
            ->setPort(SeleniumServer::PORT)
            ->start();
    }

    // This function based on https://github.com/SeleniumHQ/selenium/blob/6266e58b7cf379b8f80b125e97eb4e82a220fd09/scripts/travis/install.sh
    private function downloadGeckoDriver(string $destDirPath): string {
        $binFileName = 'geckodriver';
        $destFilePath = $destDirPath . '/' . $binFileName;
        if (is_file($destFilePath)) {
            return $destFilePath;
        }
        $curDirPath = getcwd();
        try {
            chdir($destDirPath);
            $geckoDriverDownloadUri = array_reduce(fromJson((new HttpClient())->sendGet('https://api.github.com/repos/mozilla/geckodriver/releases/latest')->getBody())['assets'], function ($acc, $asset) {
                return false !== strpos($asset['browser_download_url'], 'linux64') ? $asset['browser_download_url'] : $acc;
            });
            $arcFilePath = $destDirPath . '/geckodriver.tar.gz';
            (new HttpClient())->downloadFile($geckoDriverDownloadUri, $arcFilePath);
            cmd('tar xzf ' . escapeshellarg($arcFilePath) . ' && chmod +x ' . escapeshellarg($binFileName) . ' && rm -f ' . escapeshellarg($arcFilePath));
        } finally {
            chdir($curDirPath);
        }
        return $destFilePath;
    }
}
