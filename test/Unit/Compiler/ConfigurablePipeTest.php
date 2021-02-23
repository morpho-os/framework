<?php declare(strict_types=1);
namespace Morpho\Test\Unit\Compiler;

use Morpho\Compiler\Compiler;
use Morpho\Testing\TestCase;

class ConfigurablePipeTest extends TestCase {
    public function testConfAccessors() {
        $compiler = new Compiler();
        $this->checkAccessors([$compiler, 'conf'], [], ['foo' => 'bar'], $compiler);
    }
}