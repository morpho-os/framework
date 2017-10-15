<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\PathManager;
use const Morpho\Web\PUBLIC_DIR_NAME;

class PathManagerTest extends TestCase {
    public function testPublicDirPathAccessors() {
        $pathManager = new PathManager($this->getTestDirPath());
        $this->assertSame($this->getTestDirPath() . '/' . PUBLIC_DIR_NAME, $pathManager->publicDirPath());
        $newPublicDirPath = $this->tmpDirPath();
        $this->assertNull($pathManager->setPublicDirPath($newPublicDirPath));
        $this->assertSame($newPublicDirPath, $pathManager->publicDirPath());
    }
}