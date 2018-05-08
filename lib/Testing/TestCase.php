<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
    const TIMEZONE = 'UTC';

    private $tmpDirPaths = [];
    private $tmpFilePaths = [];

    private $classFilePath;

    private $prevTimezone;

    protected $backupGlobals = true;

    public function setUp() {
        parent::setUp();
        Vfs::register();
    }

    protected function tearDown() {
        parent::tearDown();
        if (null !== $this->prevTimezone) {
            \date_default_timezone_set($this->prevTimezone);
            $this->prevTimezone = null;
        }
        $this->deleteTmpFiles();
        $this->deleteTmpDirs();
        Vfs::unregister();
    }

    protected function createTmpFile(string $ext = null, string $prefix = null, bool $deleteOnTearDown = true): string {
        if (null === $ext) {
            $tmpFilePath = \tempnam($this->tmpDirPath(), \uniqid((string)$prefix));
        } else {
            $fileName = \uniqid((string)$prefix) . \strtolower(__FUNCTION__);
            if (null !== $ext) {
                $fileName .= '.' . \ltrim($ext, '.');
            }
            $tmpFilePath = $this->tmpDirPath() . '/' . $fileName;
            \touch($tmpFilePath);
            if (!\is_file($tmpFilePath)) {
                throw new \RuntimeException();
            }
        }
        if ($deleteOnTearDown) {
            $this->tmpFilePaths[] = $tmpFilePath;
        }
        return $tmpFilePath;
    }

    protected function tmpFilePath(): string {
        $tmpFilePath = $this->createTmpFile();
        \unlink($tmpFilePath);
        return $tmpFilePath;
    }

    protected function assertSetsEqual(array $expected, array $actual): void {
        $this->assertCount(\count($expected), $actual);
        foreach ($expected as $expect) {
            // @TODO: Better implementation, not O(n^2)?
            $this->assertContains($expect, $actual);
        }
    }

    protected function sut() {
        return Sut::instance();
    }

    /*
    protected function initSession()
    {
        try {
            (new EnvInitializer())->initSession();
        } catch (\RuntimeException $e) {
            // fallback case.
            $GLOBALS['_SESSION'] = array();
        }
    }
    */

    /**
     * Note: we can't use name testDirPath as it will be considered as test method.
     */
    protected function getTestDirPath(): string {
        $classFilePath = $this->classFilePath();
        return \dirname($classFilePath) . '/_files/' . \pathinfo($classFilePath, PATHINFO_FILENAME);
    }

    protected function createTmpDir(string $dirName = null): string {
        $tmpDirPath = $this->tmpDirPath() . '/' . \md5(\uniqid('', true));
        $this->tmpDirPaths[] = $tmpDirPath;
        $tmpDirPath .= null !== $dirName ? '/' . $dirName : '';
        if (\is_dir($tmpDirPath)) {
            throw new \RuntimeException("The directory '$tmpDirPath' is already exists.");
        }
        \mkdir($tmpDirPath, 0777, true);
        return $tmpDirPath;
    }

    protected function tmpDirPath(): string {
        return \sys_get_temp_dir();
    }

/*    protected function namespace($useFqn = false) {
        $class = get_class($this);
        return ($useFqn ? '\\' : '') . \substr($class, 0, strrpos($class, '\\'));
    }*/

    protected function assertIntString($val): void {
        $this->assertRegExp('~^[-+]?\d+$~si', $val, "The value is not either an integer or an integer string");
    }

    protected function assertHtmlEquals($expected, $actual, $message = ''): void {
        $expected = $this->normalizeHtml($expected);
        $actual = $this->normalizeHtml($actual);
        self::assertSame($expected, $actual, $message);
    }

    protected function assertVoid($value): void {
        $this->assertNull($value);
    }

    protected function normalizeHtml(string $html) {
        return \preg_replace(['~>\s+~si', '~\s+<~'], ['>', '<'], \trim($html));
    }

    protected function checkBoolAccessor(callable $callback, $initialValue): void {
        $this->assertSame($initialValue, $callback());
        $this->assertTrue($callback(true), 'Returns the passed true');
        $this->assertTrue($callback(), 'Returns the previous value that was set: true');
        $this->assertFalse($callback(false), 'Returns the passed false');
        $this->assertFalse($callback(), 'Returns the previous value that was set: false');
    }

    protected function checkAccessors(callable $getter, $initialValue, $newValue): void {
        if (!isset($getter[1]) || !\is_string($getter[1])) {
            throw new \InvalidArgumentException();
        }
        if ($initialValue instanceof \Closure) {
            $initialValue($getter());
        } else {
            $this->assertSame($initialValue, $getter());
        }
        [$object, $methodName] = $getter;
        $this->assertNull($object->{'set' . $methodName}($newValue));
        $this->assertSame($newValue, $object->$methodName());
    }

    protected function checkCanSetNull(callable $getter): void {
        if (!isset($getter[1]) || !\is_string($getter[1])) {
            throw new \InvalidArgumentException();
        }
        [$object, $methodName] = $getter;
        $this->assertNull($object->{'set' . $methodName}(null));
        $this->assertNull($getter());
    }

    protected function setDefaultTimezone(): void {
        $this->prevTimezone = @\date_default_timezone_get();
        \date_default_timezone_set(self::TIMEZONE);
    }

    protected function randomString(): string {
        return \md5(\uniqid(\microtime(true)));
    }

    protected function markTestAsNotRisky(): void {
        $this->addToAssertionCount(1);
        // $this->assertTrue(true) may work too.
    }

    protected function isWindows(): bool {
        return \defined('PHP_WINDOWS_VERSION_BUILD');
    }

    public function expectException(string $exception, $message = '', $code = null): void {
        parent::expectException($exception);
        if ($message !== null && $message !== '') {
            $this->expectExceptionMessage($message);
        }
        if ($code !== null) {
            $this->expectExceptionCode($code);
        }
    }

    protected function isTravis(): bool {
        return !empty(\getenv('TRAVIS'));
    }

    private function deleteTmpDirs(): void {
        $sysTmpDirPath = $this->tmpDirPath();
        foreach ($this->tmpDirPaths as $tmpDirPath) {
            if (\is_dir($tmpDirPath)) {
                foreach (new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($tmpDirPath, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                ) as $path => $_) {
                    if (false !== \strpos($path, $sysTmpDirPath) && $path !== $sysTmpDirPath) {
                        if (\is_dir($path)) {
                            if (false === $this->tryDeleteDir($path)) {
                                $parentDirPath = \realpath($path . '/..');
                                if ($parentDirPath !== $sysTmpDirPath) {
                                    if ($this->fixPerms($parentDirPath)) {
                                        \rmdir($path);
                                    }
                                }
                            }
                        } else {
                            if (false === $this->tryDeleteFile($path)) {
                                $parentDirPath = \realpath($path . '/..');
                                if ($parentDirPath !== $sysTmpDirPath) {
                                    if ($this->fixPerms($parentDirPath)) {
                                        \unlink($path);
                                    }
                                }
                            }
                        }
                    }
                }
                if (false !== \strpos($tmpDirPath, $sysTmpDirPath)) {
                    \rmdir($tmpDirPath);
                }
            }
        }
    }

    private function deleteTmpFiles(): void {
        foreach ($this->tmpFilePaths as $tmpFilePath) {
            if (\is_file($tmpFilePath)) {
                $this->tryDeleteFile($tmpFilePath);
            }
        }
    }

    private function classFilePath(): string {
        if (!isset($this->classFilePath)) {
            $filePath = (new \ReflectionObject($this))->getFileName();
            $isWindows = \defined('PHP_WINDOWS_VERSION_BUILD');
            $this->classFilePath = $isWindows ? \str_replace('\\', '/', $filePath) : $filePath;
        }

        return $this->classFilePath;
    }

    private function tryDeleteDir(string $dirPath): bool {
        $this->fixPerms($dirPath);
        return @\rmdir($dirPath);
    }

    private function tryDeleteFile(string $filePath): bool {
        $this->fixPerms($filePath);
        return @\unlink($filePath);
    }

    private function fixPerms(string $path): bool {
        $prevMode = @\fileperms($path) & 07777;
        if (!$prevMode) {
            return false;
        }
        return @\chmod($path, $prevMode | 0200); // set the write bit (in octal)
    }
}
