<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Infra;

use function Morpho\Base\showLn;
use Morpho\Core\Fs;
use const Morpho\Core\LIB_DIR_NAME;
use Morpho\Fs\Directory;

class AddLicenseCommand {
    public function __invoke(string $baseDirPath) {
        $baseDirPath = realpath($baseDirPath);
        if (!is_dir($baseDirPath . '/' . LIB_DIR_NAME) || !is_dir($baseDirPath . '/' . Fs::PUBLIC_DIR_NAME)) {
            throw new \UnexpectedValueException("Invalid base directory path");
        }
        $licenseText = <<<OUT
This file is part of morpho-os/framework
It is distributed under the 'Apache License Version 2.0' license.
See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
OUT;

        $licenseHeaderManager = new LicenseHeaderManager();

        $addLicenseForFiles = function (iterable $files) use ($licenseHeaderManager, $licenseText) {
            return $this->updateLicenseForFiles($licenseHeaderManager, $files, $licenseText);
        };

        $i = 0;
        $i += $addLicenseForFiles(
            Directory::filePaths($baseDirPath . '/' . LIB_DIR_NAME, null, ['recursive' => true])
        );
        $i += $addLicenseForFiles($this->filesInTestDir($baseDirPath));
        $i += $addLicenseForFiles(
            Directory::filePaths($baseDirPath . '/' . Fs::PUBLIC_DIR_NAME . '/' . Fs::MODULE_DIR_NAME, '~\.(ts|styl)$~', ['recursive' => true])
        );

        showLn("Processed $i files");
    }

    private function filesInTestDir(string $baseDirPath): iterable {
        foreach (Directory::filePaths($baseDirPath . '/' . Fs::TEST_DIR_NAME, '~[^/](Test|Suite)\.php$~s', ['recursive' => true]) as $filePath) {
            if (preg_match('~/' . preg_quote(Fs::TEST_DIR_NAME, '~') . '/.*?/_files/~s', $filePath)) {
                continue;
            }
            yield $filePath;
        }
        yield from Directory::filePaths($baseDirPath . '/' . Fs::TEST_DIR_NAME . '/visual', Directory::PHP_FILES_RE);
        yield $baseDirPath . '/' . Fs::TEST_DIR_NAME . '/bootstrap.php';
    }

    private function updateLicenseForFiles(LicenseHeaderManager $licenseHeaderManager, iterable $files, string $licenseText): int {
        $i = 0;
        foreach ($files as $filePath) {
            $licenseHeaderManager->updateLicenseHeader($filePath, $licenseText);
            $i++;
        }
        return $i;
    }
}