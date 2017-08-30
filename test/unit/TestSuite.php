<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit;

class TestSuite extends \Morpho\Test\TestSuite {
    public function testFilePaths(): iterable {
        return $this->testFilesInDir(__DIR__);
    }
}