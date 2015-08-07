<?php
namespace MorphoTest\Fs;

use Morpho\Test\TestCase;
use Morpho\Fs\NatOrderIterator;

class NatOrderIteratorTest extends TestCase {
    public function testRecursiveIterator() {
        $it = new NatOrderIterator($this->getTestDirPath(), NatOrderIterator::CURRENT_AS_FILEINFO);

        $this->assertNull($it->rewind());

        $this->assertTrue($it->valid());
        $this->assertEquals('1-file.html', $it->current()->getBasename());
        $this->assertNull($it->next());

        $this->assertTrue($it->valid());
        $this->assertEquals('2-file.html', $it->current()->getBasename());
        $this->assertNull($it->next());

        $this->assertTrue($it->valid());
        $this->assertEquals('10-file.html', $it->current()->getBasename());
        $this->assertNull($it->next());

        $this->assertTrue($it->valid());
        $this->assertEquals('30-file.html', $it->current()->getBasename());
        $this->assertNull($it->next());

        $this->assertTrue($it->valid());
        $this->assertEquals('file.html', $it->current()->getBasename());
        $this->assertNull($it->next());

        $this->assertTrue($it->valid());
        $this->assertEquals('one', $it->current()->getBasename());

        $this->assertTrue($it->hasChildren());
        $this->assertInstanceOf('\Morpho\Fs\NatOrderIterator', $it->getChildren());
        $this->assertNull($it->next());

        $this->assertFalse($it->hasChildren());

        $this->assertFalse($it->valid());
    }

    public function testThrowsOutOfBoundsExceptionWhenRewindNotCalled() {
        $it = new NatOrderIterator($this->getTestDirPath() . '/one');
        $this->setExpectedException('\OutOfBoundsException');
        $it->current();
    }

    public function dataForThrowsExceptionWhenNonExistentInitialDirectoryProvided() {
        return [
            [''],
            [$this->getTestDirPath() . '/non-existent']
        ];
    }

    /**
     * @dataProvider dataForThrowsExceptionWhenNonExistentInitialDirectoryProvided
     */
    public function testThrowsExceptionWhenNonExistentInitialDirectoryProvided($dirPath) {
        $this->setExpectedException('\Morpho\Fs\IoException', "The '$dirPath' directory does not exist.");
        $it = new NatOrderIterator($dirPath);
        $it->rewind();
    }

    public function testInterfaces() {
        $this->assertInstanceOf('\RecursiveIterator', new NatOrderIterator($this->getTestDirPath()));
    }
}
