<?php declare(strict_types=1);
namespace MorphoTest\Fs;

use Morpho\Fs\Entry;
use Morpho\Test\TestCase;

class EntryTest extends TestCase {
    public function testModeString() {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped();
        }
        $this->assertEquals("0644", Entry::modeString(__FILE__));
    }

    public function testMode() {
        $this->assertEquals(0644, Entry::mode(__FILE__));
    }
}