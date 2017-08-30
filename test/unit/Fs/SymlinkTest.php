<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Fs;

use Morpho\Fs\Directory;
use Morpho\Fs\Symlink;
use Morpho\Test\TestCase;
use Morpho\Fs\Exception as FsException;

class SymlinkTest extends TestCase {
    private $tmpDirPath;

    public function setUp() {
        parent::setUp();
        $this->tmpDirPath = $this->createTmpDir(__FUNCTION__);
    }

    public function testCreate_CreatesLinkWhenLinkIsDirAndTargetIsFile() {
        $targetFilePath = $this->tmpDirPath . '/' . md5(uniqid(__FUNCTION__));
        copy(__FILE__, $targetFilePath);
        $this->assertTrue(is_file($targetFilePath));
        $linkDirPath = Directory::create($this->tmpDirPath . '/my-link');
        $expectedLinkPath = $linkDirPath . '/' . basename($targetFilePath);
        $this->assertFalse(is_file($expectedLinkPath));
        Symlink::create($targetFilePath, $linkDirPath);
        $this->assertTrue(is_file($expectedLinkPath));
    }
    
    public function testIsBroken() {
        $tmpDirPath = $this->createTmpDir();
        symlink($tmpDirPath . '/foo', $tmpDirPath . '/bar');
        touch($tmpDirPath . '/dest');
        symlink($tmpDirPath . '/dest', $tmpDirPath . '/src');
        $this->assertTrue(Symlink::isBroken($tmpDirPath . '/bar'));
        $this->assertFalse(Symlink::isBroken($tmpDirPath . '/src'));
    }

    public function testIsBroken_ThrowsExceptionIfNotSymlinkPathPassed() {
        $this->expectException(FsException::class, "The passed path is not a symlink");
        Symlink::isBroken(__FILE__);
    }
}