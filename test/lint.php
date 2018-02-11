<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

use Morpho\Fs\Dir;
use Morpho\Fs\Path;
use Morpho\Infra\Linter;
use Morpho\Infra\Psr4MappingProvider;

require __DIR__ . '/../vendor/autoload.php';

class TestClassesMappingProvider extends Psr4MappingProvider {
    public function filePaths(): iterable {
        return Dir::filePaths($this->baseDirPath, function ($filePath) {
            return (bool) preg_match('~[^/](Test|Suite)\.php$~si', $filePath);
        }, true);
    }
}

function main(): void {
    $moduleDirPath = realpath(__DIR__ . '/..');
    $mappers = [];
    $absDirPath = function (string $relDirPath) use ($moduleDirPath) {
        return Path::combine($moduleDirPath, $relDirPath);
    };
    $fqNs = function ($ns) {
        return 'Morpho\\' . trim($ns, '\\') . '\\';
    };

    $mappers[] = new class ('Morpho\\', $absDirPath('lib')) extends Psr4MappingProvider {
        public function filePaths(): iterable {
            return Dir::filePaths($this->baseDirPath, '~\.php$~', true);
        }
    };
    $mappers[] = new class ($fqNs('Test'), $absDirPath('test')) extends Psr4MappingProvider {
        public function filePaths(): iterable {
            return Dir::filePaths($this->baseDirPath, function ($filePath) {
                return (bool) preg_match('~[^/](Test|Suite)\.php$~si', $filePath);
            }, false);
        }
    };
    $mappers[] = new class ($fqNs('Test\\Unit'), $absDirPath('test/unit')) extends TestClassesMappingProvider {};
    $mappers[] = new class ($fqNs('Test\\Functional'), $absDirPath('test/functional')) extends TestClassesMappingProvider {};
    // @TODO: Add modules
    Linter::checkModule($moduleDirPath, $mappers);
}

main();
