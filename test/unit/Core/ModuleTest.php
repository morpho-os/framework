<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
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