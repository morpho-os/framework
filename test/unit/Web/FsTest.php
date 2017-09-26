<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Fs;
use const Morpho\Web\PUBLIC_DIR_NAME;

class FsTest extends TestCase {
    public function testPublicDirPathAccessors() {
        $fs = new Fs($this->getTestDirPath());
        $this->assertSame($this->getTestDirPath() . '/' . PUBLIC_DIR_NAME, $fs->publicDirPath());
        $newPublicDirPath = $this->tmpDirPath();
        $this->assertNull($fs->setPublicDirPath($newPublicDirPath));
        $this->assertSame($newPublicDirPath, $fs->publicDirPath());
    }
}