<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php\Linting;

use Closure;
use function array_merge;
use function Morpho\Base\showLn;
use function Morpho\Base\q;
use function Morpho\App\Cli\showErrorLn;
use function Morpho\App\Cli\showOk;
use Morpho\Fs\Dir;
use function print_r;

class Linter {
    /**
     * @param string $composerFileDirPath
     * @param iterable $psr4MapperListIt
     *     Iterable yielding \Morpho\Infra\IPsr4Mapper
     * @param callable|null $lint
     * @return bool
     *     true on success, false otherwise
     */
    public function checkModule(string $composerFileDirPath, iterable $psr4MapperListIt, callable $lint = null): bool {
        showLn("Checking '$composerFileDirPath/composer.json'...");
        $metaFileErrors = ModuleChecker::checkMetaFile($composerFileDirPath . '/composer.json');
        if (null === $lint) {
            $lint = [new FileChecker(), 'checkFile'];
        }
        if ($metaFileErrors) {
            showErrorLn('Errors found:');
            showErrorLn(print_r($metaFileErrors, TRUE));
            $valid = false;
        } else {
            showOk();
            $valid = $this->checkNamespaces($psr4MapperListIt, $lint);

        }
        return $valid;
    }

    private function checkNamespaces(iterable $psr4MapperListIt, callable $lint) {
        $valid = true;
        foreach ($psr4MapperListIt as $psr4Mapper) {
            $mappingErrors = [];
            showLn('Checking files in ' . q($psr4Mapper->baseDirPath() . ' (namespace ' . q($psr4Mapper->nsPrefix()) . ')...'));
            foreach ($psr4Mapper->filePaths() as $filePath) {
                $sourceFile = new SourceFile($filePath);
                $sourceFile->setNsToDirPathMap([
                    $psr4Mapper->nsPrefix() => $psr4Mapper->baseDirPath(),
                ]);
                $checkFileErrors = $lint($sourceFile);
                $mappingErrors = array_merge($mappingErrors, $checkFileErrors);
            }
            if (!$mappingErrors) {
                showOk();
            } else {
                $this->showErrors($mappingErrors);
                $valid = false;
            }
        }
        return $valid;
    }

    public static function phpFilePaths(bool $recursive = true): Closure {
        return function (string $ns, string $baseDirPath) use ($recursive): iterable {
            return Dir::filePaths($baseDirPath, '~\.php$~', true);
        };
    }

    public static function testFilePaths(bool $recursive): Closure {
        return function (string $ns, string $baseDirPath) use ($recursive): iterable {
            return Dir::filePaths($baseDirPath, function ($filePath) {
                return str_ends_with($filePath, '.php') && !str_contains($filePath, '/_files/');
            }, $recursive);
        };
    }

    private function showErrors(array $mappingErrors): void {
        showLn('Errors found:');
        showLn(print_r($mappingErrors, true));
    }
}
