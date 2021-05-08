<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace Morpho\Net\Http;

use RuntimeException;

use function array_reduce;
use function basename;
use function chdir;
use function dirname;
use function escapeshellarg;
use function file_exists;
use function getcwd;
use function Morpho\App\Cli\sh;
use function Morpho\Base\fromJson;
use function proc_close;
use function proc_open;
use function strpos;

class GeckoDriver implements IWebDriver {
    public const HOST = 'localhost';
    public const PORT = 4444;
    private string $geckoBinFilePath;

    public static function downloadMk(string $geckoBinFilePath, string $downloadDirPath) {
        if (!file_exists($geckoBinFilePath)) {
            $geckoBinFilePath = '/usr/bin/geckodriver';
            if (!file_exists($geckoBinFilePath)) {
                $geckoBinFilePath = self::download($downloadDirPath . '/geckodriver');
            }
        }
        return new GeckoDriver($geckoBinFilePath);
    }

    public function __construct(string $geckoBinFilePath) {
        $this->geckoBinFilePath = $geckoBinFilePath;
    }

    public function start(): void {
        if (!file_exists($this->geckoBinFilePath)) {
            throw new RuntimeException("The '{$this->geckoBinFilePath}' does not exist");
        }
        $cmd = escapeshellarg($this->geckoBinFilePath) . ' > /dev/null 2>&1 &';
        proc_close(proc_open($cmd, [], $pipes));
        for ($i = 0; $i < 15; $i++) {
            if (HttpClient::serverAcceptsConnections(self::HOST, self::PORT)) {
                return;
            }
            sleep(1);
        }
        throw new RuntimeException('Unable to start server');
    }

    public function stop(): void {
        sh('killall geckodriver &>/dev/null || true');
    }

    public function __destruct() {
        $this->stop();
    }

    // This function based on https://github.com/SeleniumHQ/selenium/blob/6266e58b7cf379b8f80b125e97eb4e82a220fd09/scripts/travis/install.sh
    public static function download(string $destFilePath): string {
        $binFileName = basename($destFilePath);
        $curDirPath = getcwd();
        try {
            chdir(dirname($destFilePath));
            $fileDownloadMeta = self::fileDownloadMeta();
            $geckoDriverDownloadUri = $fileDownloadMeta['browser_download_url'];
            $arcFilePath = dirname($destFilePath) . '/geckodriver.tar.gz';
            (new HttpClient())->download($geckoDriverDownloadUri, $arcFilePath);
            sh('tar xzf ' . escapeshellarg($arcFilePath) . ' && chmod +x ' . escapeshellarg($binFileName) . ' && rm -f ' . escapeshellarg($arcFilePath), ['show' => false]);
        } finally {
            chdir($curDirPath);
        }
        return $destFilePath;
    }

    private static function fileDownloadMeta(): array {
        return array_reduce(
            fromJson(
                (new HttpClient())->get('https://api.github.com/repos/mozilla/geckodriver/releases/latest')->body()
            )['assets'],
            function ($acc, $downloadMeta) {
                if (false !== strpos($downloadMeta['browser_download_url'], 'linux64')) {
                    return $downloadMeta;
                }
                return $acc;
            }
        );
    }
}
