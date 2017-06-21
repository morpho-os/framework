<?php declare(strict_types=1);
namespace MorphoTest\Unit\Core;

use Morpho\Core\Module;
use Morpho\Test\TestCase;

class ModuleTest extends TestCase {
    private $module;

    public function setUp() {
        $this->module = new Module('fooo/bar', $this->getTestDirPath());
    }

    public function testDirPathAccessors() {
        $dirPath = 'foo/bar/baz';
        $this->module->setDirPath($dirPath);
        $this->assertEquals($dirPath, $this->module->dirPath());
    }
}