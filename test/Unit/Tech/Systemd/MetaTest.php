<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Tech\Systemd;

use Morpho\Testing\TestCase;
use Morpho\Tech\Systemd\Meta;
use Morpho\Tech\Systemd\UnitType;

class MetaTest extends TestCase {
    public function testPossibleBins() {
        $this->assertContains('systemctl', Meta::possibleBins());
    }

    public function testPossibleUnitTypes() {
        $this->assertContains(UnitType::SERVICE, Meta::possibleUnitTypes());
    }

    public function testRefs() {
        $this->assertIsArray(meta::refs());
    }
}
