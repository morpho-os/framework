<?php
namespace MorphoTest\Core;

use Morpho\Test\TestCase;
use Morpho\Web\Theme;
use Morpho\Web\Ui\PhpTemplateEngine;

class ThemeTest extends TestCase {
    public function testIsLayoutRendered() {
        $this->assertBoolAccessor([new MyTheme, 'isLayoutRendered'], false);
    }

    public function testRenderLayout() {
        $this->markTestIncomplete();
        $theme = new MyTheme();
        $theme->viewDirPath = $this->getTestDirPath();
        $theme->setTemplateEngine($this->createTemplateEngine());
        $theme->setLayout('my-layout');
        $actual = $theme->renderLayout('Page body content');
        $expected = <<<OUT
<div id="page-body">
    Page body content
</div>
OUT;
        $this->assertHtmlEquals($expected, $actual);
    }

    protected function createTemplateEngine() {
        $templateEngine = new PhpTemplateEngine($this->mock('\Morpho\Web\Ui\PluginManager'));
        $templateEngine->useCache(false);
        $templateEngine->setCacheDirPath($this->tmpDirPath());
        return $templateEngine;
    }
}

class MyTheme extends Theme {
    public $viewDirPath;

    protected function getViewDirPath() {
        return $this->viewDirPath;
    }
}
