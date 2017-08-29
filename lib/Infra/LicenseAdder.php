<?php
//declare(strict_types=1);
namespace Morpho\Infra;

use const Morpho\Base\EOL_FULL_RE;
use function Morpho\Base\contains;
use function Morpho\Base\startsWith;

class LicenseAdder {
    public function addLicenseForFile(string $filePath, string $licenseText): void {
        if (empty($licenseText)) {
            throw new \UnexpectedValueException();
        }
        $licenseLines = preg_split(EOL_FULL_RE, $licenseText);
        if (!contains($licenseLines[0], '/*')) {
            $licenseLines = $this->commentLines($licenseLines);
        }
        $text = file_get_contents($filePath);
        if (contains($text, implode("\n", $licenseLines))) {
            return;
        }
        $fileLines = $text === '' ? [] : preg_split(EOL_FULL_RE, $text);
        $startIndex = 0;
        if (count($fileLines)) {
            if (startsWith($fileLines[$startIndex], '#!')) {
                $startIndex++;
            }
            if (startsWith($fileLines[$startIndex], '<?php')) {
                $startIndex++;
            }
        }
        array_splice($fileLines, $startIndex, 0, $licenseLines);
        file_put_contents($filePath, implode("\n", $fileLines));
    }

    private function commentLines(array $licenseLines): array {
        foreach ($licenseLines as &$line) {
            $line = ' * ' . $line;
        }
        return array_merge(['/**'], $licenseLines, [' */']);
    }
}