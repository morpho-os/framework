<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test;

use Morpho\Testing\TestSuite as BaseTestSuite;

class TestSuite extends BaseTestSuite {
    public function testFilePaths(): iterable {
        (yield __DIR__ . '/ModuleTestSuite.php');
        (yield __DIR__ . '/Integration/TestSuite.php');
        (yield __DIR__ . '/Unit/TestSuite.php');
    }
}