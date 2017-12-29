<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Infra;

use function Morpho\Base\showLn;
use const Morpho\Core\LIB_DIR_NAME;
use const Morpho\Web\PUBLIC_DIR_NAME;

class AddLicenseCommand {
    public function __invoke($context) {
        $baseDirPath = realpath($context['baseDirPath']);
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
            $context['filePaths'],
            $licenseText
        );

        showLn("Processed $i files");
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