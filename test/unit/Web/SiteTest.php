<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web;

use Morpho\Test\TestCase;
use Morpho\Web\Site;

class SiteTest extends TestCase {
    public function testAccessors() {
        $moduleName = 'foo/bar';
        $hostName = 'example.com';
        $site = new Site($moduleName, $hostName);
        $this->assertSame($hostName, $site->hostName());
        $this->assertSame($moduleName, $site->moduleName());
    }
}