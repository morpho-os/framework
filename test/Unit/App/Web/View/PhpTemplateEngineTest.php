<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Base\IFn;
use Morpho\Ioc\ServiceManager;
use Morpho\Ioc\IServiceManager;
use Morpho\Testing\TestCase;
use Morpho\App\Web\Uri\Uri;
use Morpho\App\Web\View\PhpTemplateEngine;
use Morpho\App\Web\Request;

class PhpTemplateEngineTest extends TestCase {
    private PhpTemplateEngine $templateEngine;

    public function setUp(): void {
        parent::setUp();
        $serviceManager = $this->mkServiceManager();
        $this->templateEngine = new PhpTemplateEngine($serviceManager);
        $this->configureTemplateEngine($this->templateEngine);
    }

    public function dataForUriWithRedirectToSelf() {
        $curUriStr = 'http://localhost/some/base/path/abc/def?three=qux&four=pizza';
        yield [
            '/foo/bar?one=1&two=2',
            $curUriStr,
            '/some/base/path/foo/bar?one=1&two=2&redirect=' . \rawurlencode($curUriStr),
        ];
        yield [
            'http://example.com',
            $curUriStr,
            'http://example.com?redirect=' . \rawurlencode($curUriStr),
        ];
    }

    /**
     * @dataProvider dataForUriWithRedirectToSelf
     */
    public function testUriWithRedirectToSelf(string $uriStr, string $curUriStr, string $expectedUriStr) {
        $curUri = new Uri($curUriStr);
        $curUri->path()->setBasePath('/some/base/path');
        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('uri')
            ->willReturn($curUri);
        $serviceManager = $this->mkServiceManager(['request' => $request]);
        $templateEngine = new PhpTemplateEngine($serviceManager);
        $this->configureTemplateEngine($templateEngine);
        $uri = $templateEngine->uriWithRedirectToSelf($uriStr);
        $this->assertSame(
            $expectedUriStr,
            $uri
        );
    }

    public function testVar_ReadUndefinedVarThrowsException() {
        $this->expectException(\RuntimeException::class, "The template variable 'foo' was not set");
        $this->templateEngine->foo;
    }

    public function testVar_MagicMethods() {
        $templateEngine = new class ($this->templateEngine) {
            public $called;
            private $templateEngine;

            public function __construct(PhpTemplateEngine $templateEngine) {
                $this->templateEngine = $templateEngine;
            }

            public function __set($name, $value) {
                $this->called = [__FUNCTION__, \func_get_args()];
                $this->templateEngine->__set($name, $value);
            }

            public function __get($name) {
                $this->called = [__FUNCTION__, \func_get_args()];
                return $this->templateEngine->__get($name);
            }

            public function __isset($name) {
                $this->called = [__FUNCTION__, \func_get_args()];
                return $this->templateEngine->__isset($name);
            }

            public function __unset($name) {
                $this->called = [__FUNCTION__, \func_get_args()];
                $this->templateEngine->__unset($name);
            }
        };

        $this->assertEquals([], $this->templateEngine->vars());

        $this->assertEmpty($templateEngine->called);
        $this->assertFalse(isset($templateEngine->foo));
        $this->assertEquals(['__isset', ['foo']], $templateEngine->called);
        $this->assertEquals([], $this->templateEngine->vars());

        $templateEngine->called = null;
        /** @noinspection PhpUndefinedFieldInspection */
        $templateEngine->foo = 'bar';
        $this->assertEquals(['__set', ['foo', 'bar']], $templateEngine->called);
        $this->assertEquals(['foo' => 'bar'], $this->templateEngine->vars());

        $templateEngine->called = null;
        /** @noinspection PhpUndefinedFieldInspection */
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
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->templateEngine->setVars(['foo' => 'bar']));
        $this->assertEquals(['foo' => 'bar'], $this->templateEngine->vars());
        $newVals = ['baz' => 'Other', 'foo' => 'New'];
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $this->assertNull($this->templateEngine->mergeVars($newVals));
        $this->assertEquals($newVals, $this->templateEngine->vars());
    }

    public function testRunFileWithAbsPath() {
        $dirPath = $this->getTestDirPath();
        $this->assertEquals('<h1>Hello World!</h1>', $this->templateEngine->runFile($dirPath . '/my-file.phtml', ['who' => 'World!']));
    }

    public function testRunFileThrowsExceptionWhenNotExist() {
        $path = $this->getTestDirPath() . '/non-existing.phtml';
        $this->expectException('\RuntimeException', 'The file \'' . $path . '\' does not exist');
        $this->templateEngine->runFile($path);
    }

    public function testLink_FullUriWithAttributes() {
        $this->assertEquals('<a data-foo="bar" href="http://example.com/base/path/some/path?arg=val">Link text</a>', $this->templateEngine->l('http://example.com/base/path/some/path?arg=val', 'Link text', ['data-foo' => 'bar'], ['eol' => false]));
    }

    public function testLink_PrependBasePath() {
        $serviceManager = $this->mkServiceManager();
        $templateEngine = new PhpTemplateEngine($serviceManager);
        $this->configureTemplateEngine($templateEngine);

        $uri = new Uri('/one/two');
        $html = $templateEngine->l($uri, 'News');
        $this->assertSame('<a href="/base/path/one/two">News</a>', $html);
    }

    public function testInvoke_NotClosedLink() {
        $this->assertEquals('<a href="', $this->invokeTemplateEngine('<a href="'));
    }

    public function testInvoke_AbsLink() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a>',
            $this->invokeTemplateEngine('<a href="/my/link">Link text</a>')
        );
    }

    public function testInvoke_MultipleAbsLinks() {
        $this->assertEquals(
            '<a href="/base/path/my/link">Link text</a><a href="/base/path/my1/link1">Link text 1</a>',
            $this->invokeTemplateEngine('<a href="/my/link">Link text</a><a href="/my1/link1">Link text 1</a>')
        );
    }

    public function testInvoke_RelLink() {
        $html = '<a href="foo/bar">Link text</a>';
        $this->assertEquals($html, $this->invokeTemplateEngine($html));
    }

    public function testInvoke_EscapesVars() {
        $this->assertMatchesRegularExpression(
            '~^<h1>\s*<\?php\s+echo htmlspecialchars\(\$var, ENT_QUOTES\);\s+\?>\s*</h1>$~si',
            $this->invokeTemplateEngine('<h1><?= $var ?></h1>')
        );
    }

    public function testInvoke_PrintDoesNotEscapeVars() {
        $this->assertMatchesRegularExpression(
            '~^<\?php\s+print\s+\'<div><span>Text</span></div>\';$~s',
            $this->invokeTemplateEngine("<?php print '<div><span>Text</span></div>'; ?>")
        );
        $this->assertMatchesRegularExpression(
            '~^<\?php\s+print\s+"<div><span>Text</span></div>";$~s',
            $this->invokeTemplateEngine('<?php print("<div><span>Text</span></div>");')
        );
    }

    public function testInvoke_ThrowsSyntaxError() {
        $php = '<?php some invalid code; ?>';
        $this->expectException('\PhpParser\Error');
        $this->invokeTemplateEngine($php);
    }

    public function testRequire() {
        $this->assertEquals("<h1>Hey! It is &quot;just quote&quot; works!</h1>", $this->templateEngine->runFile($this->getTestDirPath() . '/require-test.phtml'));
    }

    public function testResolvesDirAndFileConstants() {
        $expected = 'Dir path: ' . $this->getTestDirPath() . ', file path: ' . $this->getTestDirPath() . '/dir-file-test.phtml';
        $this->assertEquals($expected, $this->templateEngine->runFile($this->getTestDirPath() . '/dir-file-test.phtml'));
    }

    public function testResolvesDirAndFileConstants_RequireScriptFromChildDirRequiredFromParentDir() {
        $testDirPath = $this->getTestDirPath();
        $actual = $this->templateEngine->runFile($testDirPath . '/sub-dir/includes-from-parent-dir.phtml');
        $expected = <<<OUT
Begin 1
Dir path: $testDirPath/sub-dir, file path: $testDirPath/sub-dir/includes-from-parent-dir.phtml
Begin 2
Begin 3
Dir path: $testDirPath, file path: $testDirPath/dir-file-test.phtml
End 3
End 2
Dir path: $testDirPath/sub-dir, file path: $testDirPath/sub-dir/includes-from-parent-dir.phtml
End 1
OUT;
        $this->assertSame($expected, $actual);
    }

    public function testPlugin_ReturnsTheSamePluginInstance() {
        $pluginName = 'Messenger';
        $pluginClass= TestPlugin::class;

        $pluginResolver = function (string $name) use ($pluginName) {
            if ($name === $pluginName) {
                return new TestPlugin();
            }
            $this->fail('Invalid plugin name');
        };

        $request = $this->createMock(Request::class);

        $serviceManager = $this->createMock(IServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->willReturnCallback(function ($id) use ($pluginResolver, $request) {
                if ($id === 'request') {
                    return $request;
                }
                if ($id === 'pluginResolver') {
                    return $pluginResolver;
                }
                throw new \UnexpectedValueException($id);
            });

        $this->templateEngine->setServiceManager($serviceManager);

        $plugin = $this->templateEngine->plugin($pluginName);

        $this->assertInstanceOf($pluginClass, $plugin);
        $this->assertSame($plugin, $this->templateEngine->plugin($pluginName));
    }

    public function testUri() {
        $request = $this->mkRequest();
        $uri = new Uri();
        $uri1 = $this->templateEngine->uri();
        $this->assertNotSame($uri, $uri1);
        $this->assertSame($uri1, $this->templateEngine->uri());
        $request->setUri($uri);
        $serviceManager = $this->mkServiceManager(['request' => $request]);
        $this->templateEngine->setServiceManager($serviceManager);
        $this->assertSame($uri, $this->templateEngine->uri());
    }

    public function testHandler() {
        $handlerFn = function () {
        };
        $request = new Request();
        $request->setHandler(['instance' => $handlerFn]);

        $serviceManager = $this->createMock(IServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->with('request')
            ->willReturn($request);

        $this->templateEngine->setServiceManager($serviceManager);

        $this->assertSame($handlerFn, $this->templateEngine->handler());
    }

    public function testHtmlId() {
        $this->assertEquals('foo-1-bar-2-test', $this->templateEngine->id('foo[1][bar][2][test]'));
        $this->assertEquals('foo-1-bar-2-test-1', $this->templateEngine->id('foo_1-bar_2[test]'));
        $this->assertEquals('fo-o', $this->templateEngine->id('<fo>&o\\'));
        $this->assertEquals('fo-o-1', $this->templateEngine->id('<fo>&o\\'));
        $this->assertEquals('foo-bar', $this->templateEngine->id('FooBar'));
        $this->assertEquals('foo-bar-1', $this->templateEngine->id('FooBar'));
    }

    public function testEncodeDecode_OnlySpecialChars() {
        // $specialChars taken from Zend\Escaper\EscaperTest:
        $specialChars = [
            '\'' => '&#039;',
            '"'  => '&quot;',
            '<'  => '&lt;',
            '>'  => '&gt;',
            '&'  => '&amp;',
        ];
        foreach ($specialChars as $char => $expected) {
            $encoded = $this->templateEngine->encode($char);
            $this->assertSame($expected, $encoded);
            $this->assertSame($char, $this->templateEngine->decode($encoded));
        }
    }

    public function testEncodeDecode_SpecialCharsWithText() {
        $original = '<h1>Hello</h1>';
        $encoded = $this->templateEngine->encode($original);
        $this->assertEquals('&lt;h1&gt;Hello&lt;/h1&gt;', $encoded);
        $this->assertEquals($original, $this->templateEngine->decode($encoded));
    }

    public function testEmptyAttributes() {
        $this->assertEquals('', $this->templateEngine->attributes([]));
    }

    public function testMultipleAttributes() {
        $this->assertEquals(
            ' data-api name="foo" id="some-id"',
            $this->templateEngine->attributes(['data-api', 'name' => 'foo', 'id' => 'some-id'])
        );
    }

    public function testSingleTag() {
        $attributes = ['href' => 'foo/bar.css', 'rel' => 'stylesheet'];
        $expected = '<link href="foo/bar.css" rel="stylesheet">';
        $this->assertEquals(
            $expected,
            $this->templateEngine->tag('link', $attributes, null, ['eol' => false, 'isSingle' => true])
        );
        $this->assertEquals(
            $expected,
            $this->templateEngine->singleTag('link', $attributes, ['eol' => false])
        );
    }

    public function testSingleTag_IsXmlConfParam() {
        $attributes = ['bar' => 'test'];
        $expected = '<foo bar="test" />';
        $this->assertEquals(
            $expected,
            $this->templateEngine->tag('foo', $attributes, null, ['isXml' => true, 'eol' => false, 'isSingle' => true])
        );
        $this->assertEquals(
            $expected,
            $this->templateEngine->singleTag('foo', $attributes, ['isXml' => true, 'eol' => false])
        );
    }

    public function testTag() {
        $attributes = ['href' => 'foo/bar'];
        $conf = ['eol' => false];
        $this->assertEquals('<a href="foo/bar">Hello</a>', $this->templateEngine->tag('a', $attributes, 'Hello', $conf));
    }

    public function testTag_EolConfParam() {
        $this->assertEquals("<foo></foo>", $this->templateEngine->tag('foo', [], null));
        $this->assertEquals("<foo></foo>\n", $this->templateEngine->tag('foo', [], null, ['eol' => true]));
    }

    public function testTag_EscapeTextConfParam() {
        $this->assertEquals('<foo>&quot;</foo>', $this->templateEngine->tag('foo', [], '"', ['eol' => false, 'escapeText' => true]));
        $this->assertEquals('<foo>&quot;</foo>', $this->templateEngine->tag('foo', [], '"', ['eol' => false]));
        $this->assertEquals('<foo>"</foo>', $this->templateEngine->tag('foo', [], '"', ['eol' => false, 'escapeText' => false]));
    }

    public function testCopyright() {
        $curYear = \date('Y');
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

    private function mkServiceManager($services = null): ServiceManager {
        if (null === $services) {
            $request = $this->mkRequest();
            $uri = new Uri();
            $uri->setPath('/base/path/foo/bar');
            $uri->path()->setBasePath('/base/path');
            $request->setUri($uri);
            //$request->setHandler(['foo/bar', 'Test', 'Some']);
            $services = ['request' => $request];
        }
        return new ServiceManager($services);
    }

    private function configureTemplateEngine(PhpTemplateEngine $templateEngine) {
        $templateEngine->setTargetDirPath($this->tmpDirPath());
        $templateEngine->forceCompile(true);
    }

    private function mkRequest(array $serverVars = null) {
        return new Request(
            null,
            $serverVars,
            new class implements IFn { public function __invoke($value) {} }
        );
    }

    private function invokeTemplateEngine(string $code): string {
        $context = new \ArrayObject([
            'code' => $code,
            'conf' => [
                'appendSourceInfo' => false,
            ],
        ]);
        return $this->templateEngine->__invoke($context)['code'];
    }
}

class TestPlugin {

}
