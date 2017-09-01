<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Network\Http;

use function Morpho\Base\fromJson;
use function Morpho\Cli\shell;

class GeckoDriverDownloader {
    // This function based on https://github.com/SeleniumHQ/selenium/blob/6266e58b7cf379b8f80b125e97eb4e82a220fd09/scripts/travis/install.sh
    public function __invoke(string $destDirPath): string {
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
            shell('tar xzf ' . escapeshellarg($arcFilePath) . ' && chmod +x ' . escapeshellarg($binFileName) . ' && rm -f ' . escapeshellarg($arcFilePath));
        } finally {
            chdir($curDirPath);
        }
        return $destFilePath;
    }
}