<?php declare(strict_types=1);
namespace Morpho\Qa\Test;

use function Morpho\Base\endsWith;
use function Morpho\Base\showLn;
use function Morpho\Base\startsWith;
use function Morpho\Cli\errorLn;
use Morpho\Code\Linting\FileChecker;
use Morpho\Code\Linting\SourceFile;
use const Morpho\Core\LIB_DIR_NAME;
use const Morpho\Core\TEST_DIR_NAME;
use Morpho\Fs\File;
use Morpho\Fs\Path;
use Morpho\Infra\FilesIter;

require __DIR__ . '/../vendor/autoload.php';

function filesIter(string $baseDirPath): iterable {
    foreach (new FilesIter($baseDirPath) as $filePath) {
        if (startsWith($filePath, $baseDirPath . '/' . LIB_DIR_NAME)) {
            yield $filePath;
        } elseif (startsWith($filePath, $baseDirPath . '/' . TEST_DIR_NAME) && (endsWith($filePath, 'Test.php') || endsWith($filePath, 'Suite.php'))) {
            yield $filePath;
        }
    }
}

function nsToLibDirPathMap(string $baseDirPath): array {
    static $map;
    if (!$map) {
        foreach (File::readJson($baseDirPath . '/composer.json')['autoload']['psr-4'] as $ns => $relLibDirPath) {
            $map[$ns] = Path::combine($baseDirPath, $relLibDirPath);
        }
    }
    return $map;
}

function checkFile(string $moduleDirPath, $filePath): array {
    $sourceFile = new SourceFile($filePath);
    $errors = [];
    $testDirPath = $moduleDirPath . '/' . TEST_DIR_NAME;
    if (startsWith($filePath, $testDirPath)) {
        $sourceFile->setNsToLibDirPathMap([
            'Morpho\\Qa\\Test' => $testDirPath,
            'Morpho\\Qa\\Test\\Unit' => $testDirPath . '/unit',
            'Morpho\\Qa\\Test\\Functional' => $testDirPath . '/functional',
        ]);
    } else {
        $sourceFile->setNsToLibDirPathMap(nsToLibDirPathMap($moduleDirPath));
    }
    $errors = array_merge($errors, FileChecker::checkNamespaces($sourceFile));
    $errors = array_merge($errors, FileChecker::checkClassTypes($sourceFile));
    return count($errors) ? [$filePath => $errors] : [];
}

function checkModule(string $moduleDirPath, iterable $filesIter) {
    showLn('Checking composer.json...');
    $errors = FileChecker::checkMetaFile($moduleDirPath . '/composer.json');
    if (!$errors) {
        showLn("Checking the files, please wait...");
        foreach ($filesIter as $filePath) {
            //showLn('Checking the file ' . $filePath . '...');
            $errors = array_merge($errors, checkFile($moduleDirPath, $filePath));
        }
    }
    if ($errors) {
        var_dump($errors);
        errorLn('Errors found');
    } else {
        showLn('All files are OK');
    }

}

function main(): void {
    $moduleDirPath = realpath(__DIR__ . '/..');
    checkModule($moduleDirPath, filesIter($moduleDirPath));
}

main();