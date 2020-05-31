<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Code\Linting;

use function Morpho\Base\showLn;
use function Morpho\Base\wrapQ;
use function Morpho\App\Cli\showErrorLn;
use function Morpho\App\Cli\showOk;
use Morpho\Fs\Dir;

class Linter {
    /**
     * @param string $composerFileDirPath
     * @param iterable $psr4MapperListIt
     *     Iterable yielding \Morpho\Infra\IPsr4Mapper
     * @param callable|null $lint
     * @return bool
     *     true on success, false otherwise
     */
    public static function checkModule(string $composerFileDirPath, iterable $psr4MapperListIt, callable $lint = null): bool {
        showLn("Checking '$composerFileDirPath/composer.json'...");
        $metaFileErrors = ModuleChecker::checkMetaFile($composerFileDirPath . '/composer.json');
        if (null === $lint) {
            $lint = [FileChecker::class, 'checkFile'];
        }
        $valid = true;
        if ($metaFileErrors) {
            showErrorLn('Errors found:');
            showErrorLn(\print_r($metaFileErrors, TRUE));
            $valid = false;
        } else {
            showOk();
            foreach ($psr4MapperListIt as $psr4Mapper) {
                $mappingErrors = [];
                /** @var \Morpho\Infra\IPsr4Mapper $psr4Mapper */
                showLn('Checking files in ' . wrapQ($psr4Mapper->baseDirPath() . ' (namespace ' . wrapQ($psr4Mapper->nsPrefix()) . ')...'));
                foreach ($psr4Mapper->filePaths() as $filePath) {
                    $sourceFile = new SourceFile($filePath);
                    $sourceFile->setNsToDirPathMap([
                        $psr4Mapper->nsPrefix() => $psr4Mapper->baseDirPath(),
                    ]);
                    $checkFileErrors = $lint($sourceFile);
                    $mappingErrors = \array_merge($mappingErrors, $checkFileErrors);
                }
                if (!$mappingErrors) {
                    showOk();
                } else {
                    self::showErrors($mappingErrors);
                    $valid = false;
                }
            }
        }
        return $valid;
    }

    public static function phpFilePaths(bool $recursive = true): \Closure {
        return function (string $ns, string $baseDirPath) use ($recursive): iterable {
            return Dir::filePaths($baseDirPath, '~\.php$~', true);
        };
    }

    public static function testFilePaths(bool $recursive): \Closure {
        return function (string $ns, string $baseDirPath) use ($recursive): iterable {
            return Dir::filePaths($baseDirPath, function ($filePath) {
                return (bool) \preg_match('~[^/](Test|Suite)\.php$~si', $filePath);
            }, $recursive);
        };
    }

    private static function showErrors(array $mappingErrors): void {
        showLn('Errors found:');
        showLn(\print_r($mappingErrors, true));
    }
}
