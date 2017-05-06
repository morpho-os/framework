<?php declare(strict_types=1);
namespace MorphoTest\Fs;

use Morpho\Base\Environment;
use Morpho\Fs\Stat;
use Morpho\Test\TestCase;

class StatTest extends TestCase {
    public function setUp() {
        if (Environment::isWindows()) {
            // @TODO: Check on windows.
            $this->markTestIncomplete();
        }
    }

    public function testModeString() {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped();
        }
        $this->assertEquals("0644", Stat::modeString(__FILE__));
    }

    public function testMode() {
        $this->assertEquals(0644, Stat::mode(__FILE__));
    }

    public function testIsBlockDev() {
        //$this->assertTrue(posix_mknod($tmpDirPath . '/block-dev', POSIX_S_IFBLK | $mode, $dev[0], $dev[1]));
        $path = '/dev/loop0';
        $this->assertTrue(Stat::isEntry($path));
        $this->assertTrue(Stat::isBlockDev($path));
        $this->assertFalse(Stat::isCharDev($path));
        $this->assertFalse(Stat::isNamedPipe($path));
        $this->assertFalse(Stat::isSocket($path));
    }

    public function testIsCharDev() {
        //$this->assertTrue(posix_mknod($tmpDirPath . '/char-dev', POSIX_S_IFCHR | $mode, $dev[0], $dev[1]));
        $path = '/dev/urandom';
        $this->assertTrue(Stat::isEntry($path));
        $this->assertFalse(Stat::isBlockDev($path));
        $this->assertTrue(Stat::isCharDev($path));
        $this->assertFalse(Stat::isNamedPipe($path));
        $this->assertFalse(Stat::isSocket($path));
    }

    public function testIsNamedPipe() {
        $tmpDirPath = $this->createTmpDir();
        $mode = 0777;
        //$this->assertTrue(posix_mknod($tmpDirPath . '/reg-file', POSIX_S_IFREG | $mode));
        $path = $tmpDirPath . '/fifo';
        $this->assertTrue(posix_mknod($path, POSIX_S_IFIFO | $mode));

        $this->assertTrue(Stat::isEntry($path));
        $this->assertFalse(Stat::isBlockDev($path));
        $this->assertFalse(Stat::isCharDev($path));
        $this->assertTrue(Stat::isNamedPipe($path));
        $this->assertFalse(Stat::isSocket($path));
    }

    public function testIsSocket() {
        $tmpDirPath = $this->createTmpDir();
        $dev = [125, 1];
        $mode = 0777;
        $path = $tmpDirPath . '/sock';
        $this->assertTrue(posix_mknod($path, POSIX_S_IFSOCK | $mode, $dev[0], $dev[1]));

        $this->assertTrue(Stat::isEntry($path));
        $this->assertFalse(Stat::isBlockDev($path));
        $this->assertFalse(Stat::isCharDev($path));
        $this->assertFalse(Stat::isNamedPipe($path));
        $this->assertTrue(Stat::isSocket($path));
    }

    public function testIsEntry_Link() {
        $tmpDirPath = $this->createTmpDir();
        $linkPath = $tmpDirPath . '/link';

        $targetPath = $tmpDirPath . '/foo';
        $this->assertTrue(touch($targetPath));

        $this->assertTrue(link($targetPath, $linkPath));
        $this->assertTrue(Stat::isEntry($linkPath));
    }

    public function testIsEntry_RegularFile() {
        $this->assertTrue(Stat::isEntry(__FILE__));
    }

    public function testIsEntry_Directory() {
        $this->assertTrue(Stat::isEntry(__DIR__));
    }

    public function testIsEntry_NonExistingFile() {
        $this->assertFalse(Stat::isEntry(__FILE__ . '/non'));
    }
}