<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Testing\TestCase;
use Morpho\App\Web\View\TemplateEngine;
use Morpho\App\Web\View\Theme;
use Morpho\App\Web\View\HtmlResult;

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
        \mkdir(\dirname($viewAbsFilePath), 0777, true);
        \touch($viewAbsFilePath);

        $expected = 'abcdefg123';
        $viewVars = new \ArrayObject(['k' => 'v']);

        $templateEngine = $this->createMock(TemplateEngine::class);
        $templateEngine->method('runFile')
            ->with($this->equalTo($viewAbsFilePath), $this->equalTo($viewVars))
            ->willReturn($expected);

        /** @noinspection PhpParamsInspection */
        $theme = new Theme($templateEngine);

        $theme->appendBaseDirPath($baseViewDirPath);

        $actionResult = new HtmlResult($viewName, $viewVars);
        $viewPath = $actionResult->path();
        $actionResult->setPath($viewDirPath . (strlen($viewPath) ? '/' . $viewPath : ''));
        /** @noinspection PhpParamsInspection */
        $actual = $theme->render($actionResult);

        $this->assertSame($expected, $actual);
    }
}
