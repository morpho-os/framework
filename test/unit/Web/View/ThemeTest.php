<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Qa\Test\Unit\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\TemplateEngine;
use Morpho\Web\View\Theme;
use Morpho\Web\View\View;

class ThemeTest extends TestCase {
    public function testBasePathAccessors() {
        /** @noinspection PhpParamsInspection */
        $theme = new Theme($this->createMock(TemplateEngine::class));

        $this->assertEquals([], $theme->baseDirPaths());

        $baseDirPath1 = $this->getTestDirPath() . '/foo';
        $baseDirPath2 = $this->getTestDirPath() . '/bar';

        $theme->appendBaseDirPath($baseDirPath1);

        $this->assertEquals([$baseDirPath1], $theme->baseDirPaths());

        $theme->appendBaseDirPath($baseDirPath2);

        $this->assertEquals([$baseDirPath1, $baseDirPath2], $theme->baseDirPaths());

        // Append the same path twice, it must be placed at the end to render it in FIFO order.
        $theme->appendBaseDirPath($baseDirPath1);

        $this->assertEquals([$baseDirPath2, $baseDirPath1], $theme->baseDirPaths());

        $theme->clearBaseDirPaths();

        $this->assertEquals([], $theme->baseDirPaths());
    }

    public function testRender() {
        $baseViewDirPath = $this->createTmpDir();
        $viewDirPath = 'foo';
        $viewName = 'bar';

        $viewAbsFilePath = $baseViewDirPath . '/' . $viewDirPath . '/' . $viewName . Theme::VIEW_FILE_EXT;
        mkdir(dirname($viewAbsFilePath), 0777, true);
        touch($viewAbsFilePath);

        $expected = 'abcdefg123';
        $viewVars = ['k' => 'v'];

        $templateEngine = $this->createMock(TemplateEngine::class);
        $templateEngine->method('runFile')
            ->with($this->equalTo($viewAbsFilePath), $this->equalTo($viewVars))
            ->willReturn($expected);

        /** @noinspection PhpParamsInspection */
        $theme = new Theme($templateEngine);

        $theme->appendBaseDirPath($baseViewDirPath);

        $view = new View($viewName, $viewVars);
        $view->setDirPath($viewDirPath);
        /** @noinspection PhpParamsInspection */
        $actual = $theme->render($view);

        $this->assertSame($expected, $actual);
    }
}