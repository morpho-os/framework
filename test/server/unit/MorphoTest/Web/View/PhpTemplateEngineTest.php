<?php
namespace MorphoTest\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Web\View\PluginManager;
use Morpho\Web\View\HtmlParser;
use Morpho\Web\View\Compiler;

class PhpTemplateEngineTest extends TestCase {
    public function setUp() {
        $this->engine = new PhpTemplateEngine();
        $serviceManager = new \Morpho\Web\ServiceManager([]);
        $pathManager = $this->mock('\Morpho\Web\PathManager');
        $pathManager->expects($this->any())
            ->method('uri')
            ->will($this->returnCallback(function ($val) {
                return '/base/path' . $val;
            }));
        $serviceManager->set('pathManager', $pathManager);
        $compiler = new Compiler();
        $compiler->appendSourceInfo(false);
        $this->engine->attach($compiler);
        $this->engine->attach(new HtmlParser($serviceManager));
        $this->engine->setCacheDirPath($this->getTmpDirPath());
        $this->engine->useCache(false);
        $this->setDefaultTimezone();
    }

    public function testUseCache() {
        $this->assertBoolAccessor([new PhpTemplateEngine(), 'useCache'], true);
    }

    public function testRenderFileWithAbsPath() {
        $dirPath = $this->getTestDirPath();
        $this->assertEquals('<h1>Hello World!</h1>', $this->engine->renderFile($dirPath . '/my-file.phtml', array('who' => 'World!')));
    }

    public function testRenderFileThrowsExceptionWhenNotExist() {
        $path = $this->getTestDirPath() . '/non-existing.phtml';
        $this->setExpectedException('\RuntimeException', 'The file \'' . $path . '\' was not found.');
        $this->engine->renderFile($path);
    }

    public function testCopyright() {
        $curYear = date('Y');
        $brand = 'Mices\'s';

        $startYear = $curYear - 2;
        $this->assertEquals(
            '© ' . $startYear . '-' . $curYear . ', Mices&#039;s',
            $this->engine->copyright($brand, $startYear)
        );

        $startYear = $curYear;
        $this->assertEquals(
            '© ' . $startYear . ', Mices&#039;s',
            $this->engine->copyright($brand, $startYear)
        );
    }

    public function testNotClosedLink() {
        $this->assertEquals('<a href="', $this->engine->filter('<a href="'));
    }

    public function testAbsLink() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a>',
            $this->engine->filter('<a href="/my/link">Link text</a>')
        );
    }

    public function testMultipleAbsLinks() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a><a href="/base/path/my1/link1">Link text 1</a>',
            $this->engine->filter('<a href="/my/link">Link text</a><a href="/my1/link1">Link text 1</a>')
        );
    }

    public function testRelLink() {
        $html = '<a href="foo/bar">Link text</a>';
        $this->assertEquals($html, $this->engine->filter($html));
    }

    public function testEscapesVars() {
        $this->assertRegExp('~^<h1>\s*<\?php\s+echo htmlspecialchars\(\$var, ENT_QUOTES\);\s+\?>\s*</h1>$~si', $this->engine->filter('<h1><?= $var ?></h1>'));
    }

    public function testPrintDoesntEscapeVars() {
        $php = "<?php print '<div><span>Text</span></div>'; ?>";
        $expected = '~^<\?php\s+print\s+\'<div><span>Text</span></div>\';$~';
        $this->assertRegexp($expected, $this->engine->filter($php));

        $this->assertRegexp($expected, $this->engine->filter('<?php print("<div><span>Text</span></div>");'));
    }

    public function testThrowsSyntaxError() {
        $php = '<?php some invalid code; ?>';
        $this->setExpectedException('\PhpParser\Error');
        $this->engine->filter($php);
    }

    public function testRequire() {
        $this->assertEquals("<h1>Hey! It is &quot;just quot&quot; works!</h1>", $this->engine->renderFile($this->getTestDirPath() . '/require-test.phtml'));
    }

    public function testResolvesDirAndFileConstants() {
        $expected = 'Dir path: ' . $this->getTestDirPath() . ', file path: ' . $this->getTestDirPath() . '/dir-file-test.phtml';
        $this->assertEquals($expected, $this->engine->renderFile($this->getTestDirPath() . '/dir-file-test.phtml'));
    }
}
