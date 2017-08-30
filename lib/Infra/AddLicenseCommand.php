<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text
 */
declare(strict_types=1);
namespace Morpho\Infra;

use function Morpho\Base\endsWith;
use function Morpho\Base\showLn;
use const Morpho\Core\LIB_DIR_NAME;
use const Morpho\Core\TEST_DIR_NAME;
use Morpho\Fs\Directory;
use const Morpho\Web\PUBLIC_DIR_NAME;

class AddLicenseCommand {
    public function __invoke(string $baseDirPath) {
        if (!is_dir($baseDirPath . '/' . LIB_DIR_NAME) || !is_dir($baseDirPath . '/' . PUBLIC_DIR_NAME)) {
            throw new \UnexpectedValueException("Invalid base directory path");
        }
        $licenseText = <<<OUT
This file is part of morpho-os/framework
It is distributed under the 'Apache License Version 2.0' license.
See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text
OUT;

        $licenseHeaderManager = new LicenseHeaderManager();
        /*$this->addLicenseForPhpFiles(
            Directory::filePaths($baseDirPath . '/' . LIB_DIR_NAME, null, ['recursive' => true]),
            $licenseHeaderManager,
            $licenseText
        );*/
        d($this->filesInTestDir($baseDirPath));

    }

    private function filesInTestDir(string $baseDirPath): iterable {
        foreach (Directory::filePaths($baseDirPath . '/' . TEST_DIR_NAME, '~[^/](Test|Suite)\.php$~s', ['recursive' => true]) as $filePath) {
            if (preg_match('~/' . preg_quote(TEST_DIR_NAME, '~') . '/.*?/_files/~s', $filePath)) {
                continue;
            }
            yield $filePath;
        }
    }

    private function addLicenseForPhpFiles(iterable $files, LicenseHeaderManager $licenseHeaderManager, string $licenseText): int {
        $i = 0;
        foreach ($files as $filePath) {
            if (!endsWith($filePath, '.php')) {
                throw new \UnexpectedValueException();
            }
            $licenseHeaderManager->addLicenseForFile($filePath, $licenseText);
            $i++;
        }
        showLn("Processed $i files");
        return $i;
    }
}