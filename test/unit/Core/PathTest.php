<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Core;

use Morpho\Core\Path;
use Morpho\Test\TestCase;

class PathTest extends TestCase {
    public function testToRel_ThrowsExceptionWhenBasePathNotContainedWithinPath() {
        $baseDirPath = '/foo/bar/baz/';
        $path = __DIR__;
        $this->expectException(
            \RuntimeException::class,
            "The path '" . str_replace('\\', '/', $path) . "' does not contain the base path '/foo/bar/baz'"
        );
        Path::toRel($path, $baseDirPath);
    }


}