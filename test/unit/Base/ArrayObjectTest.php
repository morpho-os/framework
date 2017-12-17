<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Base;

use Morpho\Base\ArrayObject;
use Morpho\Test\TestCase;

class ArrayObjectTest extends TestCase {
    /**
     * @var ArrayObject
     */
    private $object;

    public function setUp() {
        $this->object = new MyObject();
    }

    public function testClassFilePath() {
        $this->assertEquals(str_replace('\\', '/', __FILE__), $this->object->classFilePath());
    }

    public function testClassDirPath() {
        $this->assertEquals(str_replace('\\', '/', __DIR__), $this->object->classDirPath());
    }
}

class MyObject extends ArrayObject {
}