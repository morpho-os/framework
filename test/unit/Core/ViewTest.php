<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */

namespace MorphoTest\Unit\Core;

use Morpho\Test\TestCase;
use Morpho\Core\View;

class ViewTest extends TestCase {
    public function testIsRendered() {
        $this->checkBoolAccessor([new View('foo'), 'isRendered'], false);
    }

    public function testConstructor() {
        $vars = ['foo' => 'bar'];
        $properties = ['apple' => 'green'];
        $view = new View('test', $vars, $properties, true);
        $this->assertSame('test', $view->name());
        $this->assertSame($vars, $view->vars());
        $this->assertSame($properties, $view->properties());
        $this->assertTrue($view->isRendered());
    }
}