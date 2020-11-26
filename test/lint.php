<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

use Morpho\Tech\Php\Linting\Linter;
use Morpho\Fs\Path;
use Morpho\Infra\Psr4Mapper;

require __DIR__ . '/../vendor/autoload.php';

function main(): void {
    $baseDirPath = \realpath(__DIR__ . '/..');
    $mappers = [];
    $absDirPath = function (string $relDirPath) use ($baseDirPath) {
        return Path::combine($baseDirPath, $relDirPath);
    };
    $fqNs = function ($ns) {
        return 'Morpho\\' . (null === $ns ? '' : \trim($ns, '\\') . '\\');
    };
    $mappers[] = new Psr4Mapper($fqNs(null), $absDirPath('lib'), Linter::phpFilePaths());
    $mappers[] = new Psr4Mapper($fqNs('Test'), $absDirPath('test'), Linter::testFilePaths(true));
    // @TODO: Add modules
    exit((int)!Linter::checkModule($baseDirPath, $mappers));
}

main();
