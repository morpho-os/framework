<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\View;

class ViewTest extends TestCase {
    public function testView() {
        $vars = ['foo' => 'bar'];
        $name = 'edit';
        $view = new View('edit', $vars);
        $view->isNotVar = 123;
        $this->assertSame($name, $view->name());
        $this->assertInstanceOf(\ArrayObject::class, $view);
        $this->assertSame($vars, $view->getArrayCopy());

        $this->assertNull($view->dirPath());

        $dirPath = $this->getTestDirPath();
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($view->setDirPath($dirPath));
        $this->assertSame($dirPath, $view->dirPath());

        $newName = 'update';
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($view->setName($newName));
        $this->assertSame($dirPath . '/' . $newName, $view->path());
    }
}