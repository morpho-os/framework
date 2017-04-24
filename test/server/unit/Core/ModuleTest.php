<?php declare(strict_types=1);
namespace MorphoTest\Core;

use Morpho\Core\Module;
use Morpho\Test\TestCase;

class ModuleTest extends TestCase {
    private $module;

    public function setUp() {
        $this->module = new Module();
    }

    public function testDirPathAccessors() {
        $dirPath = 'foo/bar/baz';
        $this->module->setDirPath($dirPath);
        $this->assertEquals($dirPath, $this->module->dirPath());
    }
}