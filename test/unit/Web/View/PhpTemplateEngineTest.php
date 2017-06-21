<?php declare(strict_types=1);
namespace MorphoTest\Unit\Web\View;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Uri;
use Morpho\Web\View\HtmlParserPost;
use Morpho\Web\View\HtmlParserPre;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Web\View\Compiler;
use Morpho\Web\Request;

class PhpTemplateEngineTest extends TestCase {
    private $templateEngine;

    public function setUp() {
        $this->templateEngine = new PhpTemplateEngine();
        $compiler = new Compiler();
        $compiler->appendSourceInfo(false);
        $request = new Request();
        $request->setUri((new Uri())->setBasePath('/base/path'));
        $serviceManager = new ServiceManager(['request' => $request]);
        $this->templateEngine->attach(new HtmlParserPre($serviceManager))
            ->attach($compiler)
            ->attach(new HtmlParserPost($serviceManager, true, '', []));
        $this->templateEngine->setServiceManager($serviceManager);
        $this->templateEngine->setCacheDirPath($this->tmpDirPath());
        $this->templateEngine->useCache(false);
        $this->setDefaultTimezone();
    }

    public function testVar_ReadUndefinedVarThrowsException() {
        $this->expectException('\Morpho\Base\ItemNotSetException', "The template variable 'foo' was not set.");
        $this->templateEngine->foo;
    }
    
    public function testVar_MagicMethods() {
        $templateEngine = new class ($this->templateEngine) {
            public $called;
            private $templateEngine;

            public function __construct($templateEngine) {
                $this->templateEngine = $templateEngine;
            }
            
            public function __set($name, $value) {
                $this->called = [__FUNCTION__, func_get_args()];
                $this->templateEngine->__set($name, $value);
            }
            
            public function __get($name) {
                $this->called = [__FUNCTION__, func_get_args()];
                return $this->templateEngine->__get($name);
            }
            
            public function __isset($name) {
                $this->called = [__FUNCTION__, func_get_args()];
                return $this->templateEngine->__isset($name);
            }
            
            public function __unset($name) {
                $this->called = [__FUNCTION__, func_get_args()];
                $this->templateEngine->__unset($name);
            }
        };

        $this->assertEquals([], $this->templateEngine->vars());

        $this->assertEmpty($templateEngine->called);
        $this->assertFalse(isset($templateEngine->foo));
        $this->assertEquals(['__isset', ['foo']], $templateEngine->called);
        $this->assertEquals([], $this->templateEngine->vars());

        $templateEngine->called = null;
        $templateEngine->foo = 'bar';
        $this->assertEquals(['__set', ['foo', 'bar']], $templateEngine->called);
        $this->assertEquals(['foo' => 'bar'], $this->templateEngine->vars());

        $templateEngine->called = null;
        $this->assertEquals('bar', $templateEngine->foo);
        $this->assertEquals(['__get', ['foo']], $templateEngine->called);
        $this->assertEquals(['foo' => 'bar'], $this->templateEngine->vars());

        $templateEngine->called = null;
        $this->assertTrue(isset($templateEngine->foo));
        $this->assertEquals(['__isset', ['foo']], $templateEngine->called);
        $this->assertEquals(['foo' => 'bar'], $this->templateEngine->vars());

        $templateEngine->called = null;
        unset($templateEngine->foo);
        $this->assertEquals(['__unset', ['foo']], $templateEngine->called);
        $this->assertFalse(isset($templateEngine->foo));
        $this->assertEquals([], $this->templateEngine->vars());
    }

    public function testVarMethods() {
        $this->assertEquals([], $this->templateEngine->vars());
        $this->assertNull($this->templateEngine->setVars(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $this->templateEngine->vars());
        $newVals = ['baz' => 'Other', 'foo' => 'New'];
        $this->assertNull($this->templateEngine->mergeVars($newVals));
        $this->assertEquals($newVals, $this->templateEngine->vars());
    }

    public function testUseCache() {
        $this->checkBoolAccessor([new PhpTemplateEngine(), 'useCache'], true);
    }

    public function testRenderFileWithAbsPath() {
        $dirPath = $this->getTestDirPath();
        $this->assertEquals('<h1>Hello World!</h1>', $this->templateEngine->renderFile($dirPath . '/my-file.phtml', ['who' => 'World!']));
    }

    public function testRenderFileThrowsExceptionWhenNotExist() {
        $path = $this->getTestDirPath() . '/non-existing.phtml';
        $this->expectException('\RuntimeException', 'The file \'' . $path . '\' does not exist');
        $this->templateEngine->renderFile($path);
    }

    public function testLink_FullUriWithAttributes() {
        $this->assertEquals('<a foo="bar" href="http://example.com/base/path/some/path?arg=val">Link text</a>', $this->templateEngine->link('http://example.com/base/path/some/path?arg=val', 'Link text', ['foo' => 'bar'], ['eol' => false]));
    }

    public function testCopyright() {
        $curYear = date('Y');
        $brand = 'Mices\'s';

        $startYear = $curYear - 2;
        $this->assertEquals(
            '© ' . $startYear . '-' . $curYear . ', Mices&#039;s',
            $this->templateEngine->copyright($brand, $startYear)
        );

        $startYear = $curYear;
        $this->assertEquals(
            '© ' . $startYear . ', Mices&#039;s',
            $this->templateEngine->copyright($brand, $startYear)
        );
    }

    public function testFilter_NotClosedLink() {
        $this->assertEquals('<a href="', $this->templateEngine->filter('<a href="'));
    }

    public function testFilter_AbsLink() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a>',
            $this->templateEngine->filter('<a href="/my/link">Link text</a>')
        );
    }

    public function testFilter_MultipleAbsLinks() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a><a href="/base/path/my1/link1">Link text 1</a>',
            $this->templateEngine->filter('<a href="/my/link">Link text</a><a href="/my1/link1">Link text 1</a>')
        );
    }

    public function testFilter_RelLink() {
        $html = '<a href="foo/bar">Link text</a>';
        $this->assertEquals($html, $this->templateEngine->filter($html));
    }

    public function testFilter_EscapesVars() {
        $this->assertRegExp('~^<h1>\s*<\?php\s+echo htmlspecialchars\(\$var, ENT_QUOTES\);\s+\?>\s*</h1>$~si', $this->templateEngine->filter('<h1><?= $var ?></h1>'));
    }

    public function testFilter_PrintDoesNotEscapeVars() {
        $expected = '~^<\?php\s+print\s+\'<div><span>Text</span></div>\';$~s';
        $this->assertRegexp($expected, $this->templateEngine->filter("<?php print '<div><span>Text</span></div>'; ?>"));
        $expected = '~^<\?php\s+print\s+"<div><span>Text</span></div>";$~s';
        $this->assertRegexp($expected, $this->templateEngine->filter('<?php print("<div><span>Text</span></div>");'));
    }

    public function testFilter_ThrowsSyntaxError() {
        $php = '<?php some invalid code; ?>';
        $this->expectException('\PhpParser\Error');
        $this->templateEngine->filter($php);
    }

    public function testRequire() {
        $this->assertEquals("<h1>Hey! It is &quot;just quot&quot; works!</h1>", $this->templateEngine->renderFile($this->getTestDirPath() . '/require-test.phtml'));
    }

    public function testResolvesDirAndFileConstants() {
        $expected = 'Dir path: ' . $this->getTestDirPath() . ', file path: ' . $this->getTestDirPath() . '/dir-file-test.phtml';
        $this->assertEquals($expected, $this->templateEngine->renderFile($this->getTestDirPath() . '/dir-file-test.phtml'));
    }
}
