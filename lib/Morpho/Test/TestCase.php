<?php
namespace Morpho\Test;

use Morpho\Cli\Environment as CliEnvironment;
use Morpho\Web\Environment as WebEnvironment;
use Morpho\Fs\Directory;
use Morpho\Fs\File;

abstract class TestCase extends \PHPUnit_Framework_TestCase {
    const EPS = 0.000000001;
    const TIMEZONE = 'UTC';

    private $tmpDirPaths = [];

    private $classFilePath;

    private $oldTimezone;

    protected $backupGlobals = true;

    /**
     * Creates mock object without calling of the __construct() of the $class.
     */
    public function mock($class, $methods = [], array $arguments = [], $mockClassName = '', $callOriginalConstructor = false, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false) {
        return $this->getMock($class, $methods, $arguments, $mockClassName, $callOriginalConstructor, $callOriginalClone, $callAutoload, $cloneArguments, $callOriginalMethods);
    }

    protected function tearDown() {
        if (null !== $this->oldTimezone) {
            date_default_timezone_set($this->oldTimezone);
            $this->oldTimezone = null;
        }
        $this->deleteTmpDirs();
    }

    protected function deleteTmpDirs() {
        foreach ($this->tmpDirPaths as $tmpDirPath) {
            if (is_dir($tmpDirPath)) {
                Directory::delete($tmpDirPath);
            }
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
    
    protected function getTestDirPath(): string {
        $classFilePath = $this->getClassFilePath();
        return dirname($classFilePath) . '/_files/' . pathinfo($classFilePath, PATHINFO_FILENAME);
    }

    protected function getClassFilePath(): string {
        if (!isset($this->classFilePath)) {
            $this->classFilePath = str_replace('\\', '/', (new \ReflectionObject($this))->getFileName());
        }

        return $this->classFilePath;
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
        return Directory::tmpDirPath();
    }

    protected function copyFile($srcFilePath, $targetFilePath): string {
        return File::copy($srcFilePath, $targetFilePath);
    }

    protected function getNamespace($useFqn = false) {
        $class = get_class($this);
        return ($useFqn ? '\\' : '') . substr($class, 0, strrpos($class, '\\'));
    }

    protected function assertIntString($val) {
        $this->assertRegExp('~^[-+]?\d+$~si', $val, "The value is not either an integer or an integer string");
    }

    protected function assertHtmlEquals($expected, $actual, $message = '') {
        $expected = preg_replace(['~>\s+~si', '~\s+<~'], ['>', '<'], trim($expected));
        $actual = preg_replace(['~>\s+~si', '~\s+<~'], ['>', '<'], trim($actual));
        self::assertEquals($expected, $actual, $message);
    }

    protected function assertBoolAccessor(callable $callback, $initialValue) {
        $this->assertSame($initialValue, $callback());
        $this->assertTrue($callback(true), 'Returns the passed true');
        $this->assertTrue($callback(), 'Returns the previous value that was set: true');
        $this->assertFalse($callback(false), 'Returns the passed false');
        $this->assertFalse($callback(), 'Returns the previous value that was set: false');
    }

    protected function assertNotUniqueInstance(callable $fn, string $expectedClass) {
        $this->assertInstanceUniqueness($fn, $expectedClass, false);
    }
    
    protected function assertUniqueInstance(callable $fn, string $expectedClass) {
        $this->assertInstanceUniqueness($fn, $expectedClass, true);
    }

    protected function setDefaultTimezone() {
        $this->oldTimezone = @date_default_timezone_get();
        date_default_timezone_set(self::TIMEZONE);
    }

    protected function randomString() {
        return md5(uniqid(microtime(true)));
    }

    private function assertInstanceUniqueness(callable $fn, string $expectedClass, bool $unique) {
        $instance1 = $fn();
        $this->assertInstanceOf($expectedClass, $instance1);
        $instance2 = $fn();
        $this->assertInstanceOf($expectedClass, $instance2);
        if ($unique) {
            $this->assertNotSame($instance1, $instance2);
        } else {
            $this->assertSame($instance1, $instance2);
        }
    }
}