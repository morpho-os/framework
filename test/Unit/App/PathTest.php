<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App;

use Morpho\App\Path;
use Morpho\Testing\TestCase;
use RuntimeException;

use function str_replace;

class PathTest extends TestCase {
    public function testToRel_ThrowsExceptionWhenBasePathNotContainedWithinPath() {
        $baseDirPath = '/foo/bar/baz/';
        $path = __DIR__;
        $this->expectException(
            RuntimeException::class,
            "The path '" . str_replace('\\', '/', $path) . "' does not contain the base path '/foo/bar/baz'"
        );
        Path::rel($path, $baseDirPath);
    }
}
