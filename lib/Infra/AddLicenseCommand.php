<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Infra;

use function Morpho\Base\chain;
use function Morpho\Base\showLn;
use const Morpho\Web\LIB_DIR_NAME;
use const Morpho\Web\MODULE_DIR_NAME;
use const Morpho\Web\TEST_DIR_NAME;
use Morpho\Fs\Directory;
use const Morpho\Web\PUBLIC_DIR_NAME;

class AddLicenseCommand {
    public function __invoke(string $baseDirPath) {
        $baseDirPath = realpath($baseDirPath);
        if (!is_dir($baseDirPath . '/' . LIB_DIR_NAME) || !is_dir($baseDirPath . '/' . PUBLIC_DIR_NAME)) {
            throw new \UnexpectedValueException("Invalid base directory path");
        }
        $licenseText = <<<OUT
This file is part of morpho-os/framework
It is distributed under the 'Apache License Version 2.0' license.
See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
OUT;

        $licenseHeaderManager = new LicenseHeaderManager();

        $i = $this->updateLicenseForFiles(
            $licenseHeaderManager,
            chain(
                Directory::filePaths($baseDirPath . '/' . LIB_DIR_NAME, null, ['recursive' => true]),
                $this->filesInTestDir($baseDirPath),
                Directory::filePaths($baseDirPath . '/' . PUBLIC_DIR_NAME . '/' . MODULE_DIR_NAME, '~\.(ts|styl)$~', ['recursive' => true])
            ),
            $licenseText
        );

        showLn("Processed $i files");
    }

    private function filesInTestDir(string $baseDirPath): iterable {
        foreach (Directory::filePaths($baseDirPath . '/' . TEST_DIR_NAME, '~[^/](Test|Suite)\.php$~s', ['recursive' => true]) as $filePath) {
            if (preg_match('~/' . preg_quote(TEST_DIR_NAME, '~') . '/.*?/_files/~s', $filePath)) {
                continue;
            }
            yield $filePath;
        }
        yield from Directory::filePaths($baseDirPath . '/' . TEST_DIR_NAME . '/visual', Directory::PHP_FILES_RE);
        yield $baseDirPath . '/' . TEST_DIR_NAME . '/bootstrap.php';
    }

    private function updateLicenseForFiles(LicenseHeaderManager $licenseHeaderManager, iterable $filePaths, string $licenseText): int {
        $i = 0;
        foreach ($filePaths as $filePath) {
            $licenseHeaderManager->updateLicenseHeader($filePath, $licenseText);
            $i++;
        }
        return $i;
    }
}