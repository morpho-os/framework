<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

use Morpho\Code\Linting\Linter;
use Morpho\Fs\Path;
use Morpho\Infra\Psr4Mapper;

require __DIR__ . '/../vendor/autoload.php';

function main(): void {
    $moduleDirPath = \realpath(__DIR__ . '/..');
    $mappers = [];
    $absDirPath = function (string $relDirPath) use ($moduleDirPath) {
        return Path::combine($moduleDirPath, $relDirPath);
    };
    $fqNs = function ($ns) {
        return 'Morpho\\' . \trim($ns, '\\') . '\\';
    };
    $mappers[] = new Psr4Mapper('Morpho\\', $absDirPath('lib'), Linter::phpFilePaths());
    $mappers[] = new Psr4Mapper($fqNs('Test'), $absDirPath('test'), Linter::testFilePaths(false));
    $mappers[] = new Psr4Mapper($fqNs('Test\\Unit'), $absDirPath('test/unit'), Linter::testFilePaths(true));
    $mappers[] = new Psr4Mapper($fqNs('Test\\Functional'), $absDirPath('test/functional'), Linter::testFilePaths(true));
    // @TODO: Add modules
    exit((int)!Linter::checkModule($moduleDirPath, $mappers));
}

main();
