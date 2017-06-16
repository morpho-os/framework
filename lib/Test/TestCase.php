<?php
namespace Morpho\Test;

use Morpho\Base\Environment;
use Morpho\Fs\Directory;
use Morpho\Fs\File;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
    const EPS = 0.000000001;
    const TIMEZONE = 'UTC';

    private $tmpDirPaths = [];
    private $tmpFilePaths = [];

    private $classFilePath;

    private $prevTimezone;

    protected $backupGlobals = true;

    protected function tearDown() {
        if (null !== $this->prevTimezone) {
            date_default_timezone_set($this->prevTimezone);
            $this->prevTimezone = null;
        }
        $this->deleteTmpFiles();
        $this->deleteTmpDirs();
    }

    protected function createTmpFile(string $ext = null, string $prefix = null): string {
        $fileName = uniqid($prefix) . strtolower(__FUNCTION__);
        if (null !== $ext) {
            $fileName .= '.' . ltrim($ext, '.');
        }
        $filePath = $this->tmpDirPath() . '/' . $fileName;
        return $this->tmpFilePaths[] = File::createEmpty($filePath);
    }

    protected function assertSetsEqual(array $expected, array $actual) {
        $this->assertCount(count($expected), $actual);
        foreach ($expected as $expect) {
            // @TODO: Better implementation, not O(n^2)?
            $this->assertContains($expect, $actual);
        }
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
        return dirname($classFilePath) . '/_files/' . pathinfo($classFilePath, PATHINFO_FILENAME);
    }

    protected function createTmpDir($dirName = null): string {
        $tmpDirPath = $this->tmpDirPath() . '/' . md5(uniqid('', true));
        $this->tmpDirPaths[] = $tmpDirPath;
        $tmpDirPath .= null !== $dirName ? '/' . $dirName : '';
        if (is_dir($tmpDirPath)) {
            throw new \RuntimeException("The directory '$tmpDirPath' is already exists.");
        }

        return Directory::create($tmpDirPath);
    }

    protected function tmpDirPath(): string {
        return Directory::tmpPath();
    }

    protected function copyFile($srcFilePath, $targetFilePath): string {
        return File::copy($srcFilePath, $targetFilePath);
    }

/*    protected function namespace($useFqn = false) {
        $class = get_class($this);
        return ($useFqn ? '\\' : '') . substr($class, 0, strrpos($class, '\\'));
    }*/

    protected function assertIntString($val) {
        $this->assertRegExp('~^[-+]?\d+$~si', $val, "The value is not either an integer or an integer string");
    }

    protected function assertHtmlEquals($expected, $actual, $message = '') {
        $expected = $this->normalizeHtml($expected);
        $actual = $this->normalizeHtml($actual);
        self::assertEquals($expected, $actual, $message);
    }

    protected function normalizeHtml(string $html) {
        return preg_replace(['~>\s+~si', '~\s+<~'], ['>', '<'], trim($html));
    }

    protected function checkBoolAccessor(callable $callback, $initialValue) {
        $this->assertSame($initialValue, $callback());
        $this->assertTrue($callback(true), 'Returns the passed true');
        $this->assertTrue($callback(), 'Returns the previous value that was set: true');
        $this->assertFalse($callback(false), 'Returns the passed false');
        $this->assertFalse($callback(), 'Returns the previous value that was set: false');
    }

    protected function checkAccessors($object, $initialValue, $newValue, $methodName) {
        if ($initialValue instanceof \Closure) {
            $initialValue($object->$methodName());
        } else {
            $this->assertSame($initialValue, $object->$methodName());
        }
        $this->assertSame($object, $object->{'set' . $methodName}($newValue));
        $this->assertSame($newValue, $object->$methodName());
    }

    protected function setDefaultTimezone() {
        $this->prevTimezone = @date_default_timezone_get();
        date_default_timezone_set(self::TIMEZONE);
    }

    protected function randomString() {
        return md5(uniqid(microtime(true)));
    }

    protected function markTestAsNotRisky() {
        $this->addToAssertionCount(1);
        // $this->assertTrue(true) may work too.
    }

    protected function windowsSys(): bool {
        return Environment::isWindows();
    }

    public function expectException($exception, $message = '', $code = null) {
        parent::expectException($exception);
        if ($message !== null && $message !== '') {
            $this->expectExceptionMessage($message);
        }
        if ($code !== null) {
            $this->expectExceptionCode($code);
        }
    }

    protected function isTravis(): bool {
        return !empty(getenv('TRAVIS'));
    }

    private function deleteTmpDirs() {
        foreach ($this->tmpDirPaths as $tmpDirPath) {
            if (is_dir($tmpDirPath)) {
                Directory::delete($tmpDirPath);
            }
        }
    }

    private function deleteTmpFiles() {
        foreach ($this->tmpFilePaths as $tmpFilePath) {
            if (is_file($tmpFilePath)) {
                File::delete($tmpFilePath);
            }
        }
    }

    private function classFilePath(): string {
        if (!isset($this->classFilePath)) {
            $this->classFilePath = str_replace('\\', '/', (new \ReflectionObject($this))->getFileName());
        }

        return $this->classFilePath;
    }
}