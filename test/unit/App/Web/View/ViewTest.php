<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Testing\TestCase;
use Morpho\App\Web\View\ViewResult;

class ViewTest extends TestCase {
    public function testView() {
        $vars = ['foo' => 'bar'];
        $name = 'edit';
        $view = new ViewResult('edit', $vars);
        $view->isNotVar = 123;
        $this->assertSame($name, $view->name());
        $this->assertInstanceOf(\ArrayObject::class, $view->vars());
        $this->assertSame($vars, $view->vars()->getArrayCopy());

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
