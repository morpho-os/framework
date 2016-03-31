<?php
namespace MorphoTest\Fs;

use Morpho\Fs\Directory;
use Morpho\Fs\Symlink;
use Morpho\Test\TestCase;

class SymlinkTest extends TestCase {
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
}