<?php
namespace MorphoTest\Fs;

use Morpho\Fs\Entry;
use Morpho\Test\TestCase;

class EntryTest extends TestCase {
    public function testMode() {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped();
        }
        $this->assertEquals("0644", Entry::modeString(__FILE__));
    }
}