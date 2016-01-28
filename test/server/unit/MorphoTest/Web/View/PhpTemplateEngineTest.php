<?php
namespace MorphoTest\Web\View;

use Morpho\Web\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Uri;
use Morpho\Web\View\HtmlParserPost;
use Morpho\Web\View\HtmlParserPre;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Web\View\Compiler;
use Morpho\Web\Request;

class PhpTemplateEngineTest extends TestCase {
    public function setUp() {
        $this->engine = new PhpTemplateEngine();
        $this->initCliEnv();
        $compiler = new Compiler();
        $compiler->appendSourceInfo(false);
        $request = new Request();
        $request->setUri((new Uri())->setBasePath('/base/path'));
        $serviceManager = new ServiceManager(null, ['request' => $request]);
        $this->engine->attach(new HtmlParserPre($serviceManager))
            ->attach($compiler)
            ->attach(new HtmlParserPost($serviceManager, true, ''));
        $this->engine->setServiceManager($serviceManager);
        $this->engine->setCacheDirPath($this->getTmpDirPath());
        $this->engine->useCache(false);
        $this->setDefaultTimezone();
    }

    public function testUseCache() {
        $this->assertBoolAccessor([new PhpTemplateEngine(), 'useCache'], true);
    }

    public function testRenderFileWithAbsPath() {
        $dirPath = $this->getTestDirPath();
        $this->assertEquals('<h1>Hello World!</h1>', $this->engine->renderFile($dirPath . '/my-file.phtml', ['who' => 'World!']));
    }

    public function testRenderFileThrowsExceptionWhenNotExist() {
        $path = $this->getTestDirPath() . '/non-existing.phtml';
        $this->setExpectedException('\RuntimeException', 'The file \'' . $path . '\' was not found.');
        $this->engine->renderFile($path);
    }

    public function testLink_FullUriWithAttributes() {
        $this->assertEquals('<a foo="bar" href="http://example.com/base/path/some/path?arg=val">Link text</a>', $this->engine->link('http://example.com/base/path/some/path?arg=val', 'Link text', ['foo' => 'bar'], ['eol' => false]));
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

    public function testFilter_NotClosedLink() {
        $this->assertEquals('<a href="', $this->engine->filter('<a href="'));
    }

    public function testFilter_AbsLink() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a>',
            $this->engine->filter('<a href="/my/link">Link text</a>')
        );
    }

    public function testFilter_MultipleAbsLinks() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a><a href="/base/path/my1/link1">Link text 1</a>',
            $this->engine->filter('<a href="/my/link">Link text</a><a href="/my1/link1">Link text 1</a>')
        );
    }

    public function testFilter_RelLink() {
        $html = '<a href="foo/bar">Link text</a>';
        $this->assertEquals($html, $this->engine->filter($html));
    }

    public function testFilter_EscapesVars() {
        $this->assertRegExp('~^<h1>\s*<\?php\s+echo htmlspecialchars\(\$var, ENT_QUOTES\);\s+\?>\s*</h1>$~si', $this->engine->filter('<h1><?= $var ?></h1>'));
    }

    public function testFilter_PrintDoesNotEscapeVars() {
        $php = "<?php print '<div><span>Text</span></div>'; ?>";
        $expected = '~^<\?php\s+print\s+\'<div><span>Text</span></div>\';$~';
        $this->assertRegexp($expected, $this->engine->filter($php));

        $this->assertRegexp($expected, $this->engine->filter('<?php print("<div><span>Text</span></div>");'));
    }

    public function testFilter_ThrowsSyntaxError() {
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
