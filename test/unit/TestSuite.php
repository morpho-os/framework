<?php
declare(strict_types=1);
namespace MorphoTest\Unit;

class TestSuite extends \Morpho\Test\TestSuite {
    public function testFilePaths(): iterable {
        return $this->testFilesInDir(__DIR__);
    }
}