<?php declare(strict_types=1);
namespace Morpho\Qa\Test;

use function Morpho\Base\endsWith;
use function Morpho\Base\startsWith;
use Morpho\Code\Linting\SourceFile;
use const Morpho\Core\LIB_DIR_NAME;
use const Morpho\Core\TEST_DIR_NAME;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use Morpho\Infra\FilesIter;
use Morpho\Infra\Linter;

require __DIR__ . '/../vendor/autoload.php';

function main(): void {
    $moduleDirPath = realpath(__DIR__ . '/..');

    $filesIter = function (string $baseModuleDirPath): iterable {
        foreach (new FilesIter($baseModuleDirPath) as $filePath) {
            if (startsWith($filePath, $baseModuleDirPath . '/' . LIB_DIR_NAME)) {
                yield $filePath;
            } elseif (startsWith($filePath, $baseModuleDirPath . '/' . TEST_DIR_NAME) && (endsWith($filePath, 'Test.php') || endsWith($filePath, 'Suite.php'))) {
                yield $filePath;
            }
        }
    };

    $initSourceFile = function (SourceFile $sourceFile) {
        $moduleDirPath = $sourceFile->moduleDirPath();
        $testDirPath = $moduleDirPath . '/' . TEST_DIR_NAME;
        if (startsWith($sourceFile->filePath(), $testDirPath)) {
            $sourceFile->setNsToLibDirPathMap([
                'Morpho\\Qa\\Test' => $testDirPath,
                'Morpho\\Qa\\Test\\Unit' => $testDirPath . '/unit',
                'Morpho\\Qa\\Test\\Functional' => $testDirPath . '/functional',
            ]);
        } else {
            static $nsToLibDirPathMap;
            if (!$nsToLibDirPathMap) {
                foreach (File::readJson($moduleDirPath . '/composer.json')['autoload']['psr-4'] as $ns => $relLibDirPath) {
                    $nsToLibDirPathMap[$ns] = Path::combine($moduleDirPath, $relLibDirPath);
                }
            }
            $sourceFile->setNsToLibDirPathMap($nsToLibDirPathMap);
        }
    };

    Linter::showResult(
        Linter::checkModule($moduleDirPath, $filesIter($moduleDirPath), $initSourceFile)
    );
}

main();
