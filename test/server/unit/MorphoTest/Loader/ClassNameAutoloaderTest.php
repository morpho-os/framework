<?php
namespace MorphoTest\Loader;

use Morpho\Test\TestCase;
use Morpho\Loader\ClassNameAutoloader;

class ClassNameAutoloaderTest extends TestCase {
    public function testAutoloaderWorksForSubnamespaces() {
        $loader = new ClassNameAutoloader(false);
        $loader->mapNsToPath('\\MorphoTest\Loader\ClassNameAutoloaderTest', $this->getTestDirPath());
        $class = '\MorphoTest\Loader\ClassNameAutoloaderTest\Foo';
        $this->assertEquals($class, $loader->autoload($class));
    }
}
