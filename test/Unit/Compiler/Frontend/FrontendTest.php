<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Compiler\Frontend;

use Morpho\Compiler\Frontend\Frontend;
use Morpho\Compiler\Frontend\IFrontend;
use Morpho\Compiler\ICompilerStep;
use Morpho\Test\Unit\Compiler\ConfigurablePipeTest;

class FrontendTest extends ConfigurablePipeTest {
    public function testInterface() {
        $this->assertInstanceOf(
            ICompilerStep::class,
            new class implements IFrontend {
                public function __invoke(mixed $val): mixed {
                }
            }
        );
        $frontend = new Frontend();
        $this->assertInstanceOf(IFrontend::class, $frontend);
    }
}