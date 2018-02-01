<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

use function Morpho\Base\showLn;
use function Morpho\Cli\errorLn;
use Morpho\Code\Linting\FileChecker;
use Morpho\Code\Linting\ModuleChecker;
use Morpho\Code\Linting\SourceFile;
use const Morpho\Core\LIB_DIR_NAME;
use Morpho\Fs\Dir;

class Linter {
    public static function checkModule(string $moduleDirPath, iterable $filesIter, callable $initSourceFile): array {
        showLn('Checking composer.json...');
        $errors = ModuleChecker::checkMetaFile($moduleDirPath . '/composer.json');
        if (!$errors) {
            showLn("Checking the files...");
            foreach ($filesIter as $filePath) {
                //showLn('Checking the file ' . $filePath . '...');
                $sourceFile = new SourceFile($filePath);
                $sourceFile->setModuleDirPath($moduleDirPath);
                $initSourceFile($sourceFile);
                $errors = array_merge($errors, FileChecker::checkFile($sourceFile));
            }
        }
        return $errors;
    }

    public static function showResult(array $errors): void {
        if ($errors) {
            var_dump($errors);
            errorLn('Errors found');
        } else {
            showLn('All files are OK');
        }
    }

    public static function libDirIter(string $baseDirPath): iterable {
        return Dir::filePaths($baseDirPath . '/' . LIB_DIR_NAME, '~\.php$~s', true);
    }
}