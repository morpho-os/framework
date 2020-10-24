<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

use const Morpho\Base\EOL_FULL_RE;
use function Morpho\Base\contains;
use function Morpho\Base\hasPrefix;

class LicenseHeaderManager {
    public function updateLicenseHeader(string $filePath, string $licenseText): void {
        if (!$this->isValidLicenseText($licenseText)) {
            throw new \InvalidArgumentException("The license text must contain the both words: 'license' and 'morpho'");
        }
        if (!contains($licenseText, '/*')) {
            $licenseText = $this->commentOutLicenseText($licenseText);
        }

        $fileText = \file_get_contents($filePath);

        $res = $this->findLicenseHeaderInText($fileText);
        if (false !== $res) {
            [$oldLicenseText, $offset] = $res;
            $newFileText = \substr_replace($fileText, $licenseText, $offset, \strlen($oldLicenseText));
        } else {
            $fileLines = $fileText === '' ? [] : \preg_split(EOL_FULL_RE, $fileText);
            $startIndex = 0;
            if (\count($fileLines)) {
                if (hasPrefix($fileLines[$startIndex], '#!')) {
                    $startIndex++;
                }
                if (hasPrefix($fileLines[$startIndex], '<?php')) {
                    $startIndex++;
                }
            }
            $licenseLines = \preg_split(EOL_FULL_RE, $licenseText);
            \array_splice($fileLines, $startIndex, 0, $licenseLines);
            $newFileText = \implode("\n", $fileLines);
        }
        \file_put_contents($filePath, $newFileText);
    }

    /**
     * @return array|false
     */
    public function findLicenseHeaderInFile(string $filePath) {
        // Must return tuple ($offset, $length) or ($startLineNo, $endLineNo).
        $fileText = \file_get_contents($filePath);
        return $this->findLicenseHeaderInText($fileText);
    }

    /**
     * @return array|false
     */
    public function findLicenseHeaderInText(string $text) {
        if (\preg_match('~/\* (?:.|\n)*? \*/~xs', $text, $match, PREG_OFFSET_CAPTURE) && $this->isValidLicenseText($match[0][0])) {
            return $match[0];
        }
        return false;
    }

    public function removeLicenseHeader(string $filePath): void {
        $fileText = \file_get_contents($filePath);
        $res = $this->findLicenseHeaderInText($fileText);
        if (false !== $res) {
            [$oldLicenseText, $offset] = $res;
            $newFileText = \substr_replace($fileText, '', $offset, \strlen($oldLicenseText));
            if (\preg_match(EOL_FULL_RE, $newFileText, $match, PREG_OFFSET_CAPTURE, $offset)) {
                $newFileText = \substr_replace($newFileText, '', $match[0][1], \strlen($match[0][0]));
            }
            \file_put_contents($filePath, $newFileText);
        }
    }

    protected function commentOutLicenseText(string $licenseText): string {
        $lines = \preg_split(EOL_FULL_RE, $licenseText);
        foreach ($lines as &$line) {
            $line = ' * ' . $line;
        }
        return \implode("\n", \array_merge(['/**'], $lines, [' */']));
    }

    protected function isValidLicenseText(string $licenseText): bool {
        return \preg_match('~\blicense\b~si', $licenseText) && \preg_match('~\morpho\b~si', $licenseText);
    }
}
