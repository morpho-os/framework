<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\View;

use Morpho\Base\ItemNotSetException;
use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Uri;
use Morpho\Web\View\PostHtmlParser;
use Morpho\Web\View\PreHtmlParser;
use Morpho\Web\View\MessengerPlugin;
use Morpho\Web\View\PhpTemplateEngine;
use Morpho\Web\View\Compiler;
use Morpho\Web\Request;

class PhpTemplateEngineTest extends TestCase {
    private $templateEngine;

    public function setUp() {
        $this->templateEngine = new PhpTemplateEngine();

        $serviceManager = $this->newServiceManager();

        $compiler = new Compiler();
        $compiler->appendSourceInfo(false);
        $this->templateEngine->append(new PreHtmlParser($serviceManager))
            ->append($compiler)
            ->append(new PostHtmlParser($serviceManager));

        $this->templateEngine->setServiceManager($serviceManager);

        $this->templateEngine->setCacheDirPath($this->tmpDirPath());
        $this->templateEngine->useCache(false);
        $this->setDefaultTimezone();
    }

    public function testVar_ReadUndefinedVarThrowsException() {
        $this->expectException(ItemNotSetException::class, "The template variable 'foo' was not set.");
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

    public function testInvoke_NotClosedLink() {
        $this->assertEquals('<a href="', $this->templateEngine->__invoke('<a href="'));
    }

    public function testInvoke_AbsLink() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a>',
            $this->templateEngine->__invoke('<a href="/my/link">Link text</a>')
        );
    }

    public function testInvoke_MultipleAbsLinks() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a><a href="/base/path/my1/link1">Link text 1</a>',
            $this->templateEngine->__invoke('<a href="/my/link">Link text</a><a href="/my1/link1">Link text 1</a>')
        );
    }

    public function testInvoke_RelLink() {
        $html = '<a href="foo/bar">Link text</a>';
        $this->assertEquals($html, $this->templateEngine->__invoke($html));
    }

    public function testInvoke_EscapesVars() {
        $this->assertRegExp('~^<h1>\s*<\?php\s+echo htmlspecialchars\(\$var, ENT_QUOTES\);\s+\?>\s*</h1>$~si', $this->templateEngine->__invoke('<h1><?= $var ?></h1>'));
    }

    public function testInvoke_PrintDoesNotEscapeVars() {
        $expected = '~^<\?php\s+print\s+\'<div><span>Text</span></div>\';$~s';
        $this->assertRegexp($expected, $this->templateEngine->__invoke("<?php print '<div><span>Text</span></div>'; ?>"));
        $expected = '~^<\?php\s+print\s+"<div><span>Text</span></div>";$~s';
        $this->assertRegexp($expected, $this->templateEngine->__invoke('<?php print("<div><span>Text</span></div>");'));
    }

    public function testInvoke_ThrowsSyntaxError() {
        $php = '<?php some invalid code; ?>';
        $this->expectException('\PhpParser\Error');
        $this->templateEngine->__invoke($php);
    }

    public function testRequire() {
        $this->assertEquals("<h1>Hey! It is &quot;just quot&quot; works!</h1>", $this->templateEngine->renderFile($this->getTestDirPath() . '/require-test.phtml'));
    }

    public function testResolvesDirAndFileConstants() {
        $expected = 'Dir path: ' . $this->getTestDirPath() . ', file path: ' . $this->getTestDirPath() . '/dir-file-test.phtml';
        $this->assertEquals($expected, $this->templateEngine->renderFile($this->getTestDirPath() . '/dir-file-test.phtml'));
    }
    
    public function testPlugin_ReturnsTheSamePluginInstance() {
        $serviceManager = $this->newServiceManager();
        $serviceManager->set('moduleProvider', new class (__CLASS__ . '\\Foo') {
            private $ns;

            public function __construct($ns) {
                $this->ns = $ns;
            }

            public function offsetGet($name) {
                return new class ($this->ns) {
                    private $ns;

                    public function __construct($ns) {
                        $this->ns = $ns;
                    }

                    public function namespace(): string {
                        return $this->ns;
                    }
                };
            }
        });
        $this->templateEngine->setServiceManager($serviceManager);

        $pluginName = 'messenger';
        $plugin = $this->templateEngine->plugin($pluginName);
        $this->assertInstanceOf(MessengerPlugin::class, $plugin);
        $this->assertSame($plugin, $this->templateEngine->plugin($pluginName));
    }

    private function newServiceManager(): ServiceManager {
        $request = new Request();
        $request->setUri((new Uri())->setBasePath('/base/path'));
        $request->setHandler(['foo/bar', 'Test', 'Some']);
        $serviceManager = new ServiceManager(['request' => $request]);
        return $serviceManager;
    }
}
