<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

use function Morpho\Base\showLn;
use function Morpho\Base\wrapQ;
use function Morpho\Cli\showErrorLn;
use function Morpho\Cli\showOk;
use Morpho\Code\Linting\FileChecker;
use Morpho\Code\Linting\ModuleChecker;
use Morpho\Code\Linting\SourceFile;

class Linter {
    /**
     * @param string $moduleDirPath
     * @param iterable Iterable yielding \Morpho\Infra\IPsr4MappingProvider
     * @return array
     */
    public static function checkModule(string $moduleDirPath, iterable $mappers): array {
        showLn('Checking composer.json...');
        $errors = ModuleChecker::checkMetaFile($moduleDirPath . '/composer.json');
        if (!$errors) {
            showOk();
            foreach ($mappers as $mapper) {
                $mappingErrors = [];
                /** @var \Morpho\Infra\IPsr4MappingProvider $mapper */
                showLn('Checking ' . wrapQ($mapper->nsPrefix()) . ' -> ' . wrapQ($mapper->baseDirPath()));
                foreach ($mapper->filePaths() as $filePath) {
                    $sourceFile = new SourceFile($filePath);
                    $sourceFile->setNsToDirPathMap([
                        $mapper->nsPrefix() => $mapper->baseDirPath(),
                    ]);
                    $checkFileErrors = FileChecker::checkFile($sourceFile);
                    $mappingErrors = array_merge($mappingErrors, $checkFileErrors);
                }
                if (!$mappingErrors) {
                    showOk();
                } else {
                    $errors = array_merge($errors, $mappingErrors);
                    showErrorLn('Some errors found');
                }
            }
        } else {
            showErrorLn('Some errors found');
        }
        return $errors;
    }
}