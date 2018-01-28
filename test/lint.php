<?php declare(strict_types=1);
namespace Morpho\Qa\Test;

use function Morpho\Base\showLn;
use function Morpho\Base\startsWith;
use Morpho\Code\Linting\FileChecker;
use Morpho\Code\Linting\SourceFile;
use const Morpho\Core\LIB_DIR_NAME;
use Morpho\Infra\FilesIter;

require __DIR__ . '/../vendor/autoload.php';

function filesIter(string $baseDirPath): iterable {
    foreach (new FilesIter($baseDirPath) as $filePath) {
        if (startsWith($filePath, $baseDirPath . '/' . LIB_DIR_NAME)) {
            yield $filePath;
        }
    }
}

function checkFiles() {
    $baseDirPath = realpath(__DIR__ . '/..');
    $checker = new FileChecker();
    $errors = [];
    foreach (filesIter($baseDirPath) as $filePath) {
        showLn('Checking the file ' . $filePath . '...');
        $sourceFile = new SourceFile($filePath);
        $sourceFile->setModuleDirPath($baseDirPath);
        $errs = $checker->checkFile($sourceFile);
        if ($errs) {
            $errors[$filePath] = $errors;
        }
    }
    d($errors);
}

checkFiles();
