<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use ArrayObject;
use Morpho\App\ISite;
use Morpho\App\Web\IRequest;
use Morpho\App\Web\Request;
use Morpho\Uri\Uri;
use Morpho\App\Web\View\ScriptProcessor;
use Morpho\Testing\TestCase;
use RuntimeException;

use const Morpho\App\FRONTEND_DIR_NAME;

class ScriptProcessorTest extends TestCase {
    private ScriptProcessor $processor;
    private string $baseUriPath;

    public function setUp(): void {
        parent::setUp();
        $this->baseUriPath = '/base/path';
        $this->processor = new ScriptProcessor($this->mkRequest('foo/bar'), $this->mkSiteStub('abc/efg'));
    }

    private function mkSiteStub(string $siteModuleName): ISite {
        $site = $this->createStub(ISite::class);
        $site->method('moduleName')
            ->willReturn($siteModuleName);
        $site->method('conf')
            ->willReturn(
                [
                    'paths' => [
                        'frontendModuleDirPath' => $this->getTestDirPath() . '/' . FRONTEND_DIR_NAME,
                        'baseUriPath'           => $this->baseUriPath,
                    ],
                ]
            );
        return $site;
    }

    public function testHandlingOfScripts_InChildParentPages() {
        $childPage = <<<OUT
This
<script src="/foo/child.js"></script>
is a child
OUT;

        // processor should save child scripts
        $this->assertMatchesRegularExpression('~^This\\s+?is a child$~', $this->processor->__invoke($childPage));

        $parentPage = <<<OUT
<body>
This is a
<script src="/bar/parent.js"></script>
parent
</body>
OUT;
        // And now render them for <body>
        $html = $this->processor->__invoke($parentPage);

        $re = $this->quotedRe(
            [
                '<body>',
                'This is a',
                'parent',
                '<script src="' . $this->baseUriPath . '/bar/parent.js"></script>',
                '<script src="' . $this->baseUriPath . '/foo/child.js"></script>',
                '</body>',
            ]
        );

        $this->assertMatchesRegularExpression($re, $html);
    }

    private function quotedRe(array $parts): string {
        return '~^' . implode('\s*?', array_map(fn ($s) => preg_quote($s), $parts)) . '$~s';
    }

    public function testHandlingOfScripts_IndexAttribute() {
        $childPage = <<<OUT
This
<script src="foo/child.js"></script>
is a child
OUT;

        // processor should save child scripts
        $this->processor->__invoke($childPage);

        $indexAttr = ScriptProcessor::INDEX_ATTR;
        $parentPage = <<<OUT
<body>
This is a
<script src="bar/parent.js" ${indexAttr}="100"></script>
parent
</body>
OUT;
        // And now render them for <body>
        $html = $this->processor->__invoke($parentPage);

        $re = $this->quotedRe(
            [
                '<body>',
                'This is a',
                'parent',
                '<script src="foo/child.js"></script>',
                '<script src="bar/parent.js"></script>',
                '</body>',
            ]
        );
        $this->assertMatchesRegularExpression($re, $html);
    }

    public function dataSkipAttribute() {
        return [
            [
                'body',
            ],
            [
                'script',
            ],
        ];
    }

    /**
     * @dataProvider dataSkipAttribute
     */
    public function testSkipAttribute($tag) {
        $processor = new class ($this->mkRequest('foo'), $this->mkSiteStub('abc/efg')) extends ScriptProcessor {
            protected function containerBody(array $tag): null|array|bool {
                $res = parent::containerBody($tag);
                if (isset($res['_skip'])) {
                    throw new RuntimeException("The _skip attribute must be removed");
                }
                return $res;
            }

            protected function containerScript(array $tag): null|array|bool {
                $res = parent::containerScript($tag);
                if (isset($res['_skip'])) {
                    throw new RuntimeException("The _skip attribute must be removed");
                }
                return $res;
            }
        };

        $html = '<' . $tag . ' _skip></' . $tag . '>';
        $this->assertSame("<$tag></$tag>", $processor->__invoke($html));
    }

    public function testSkipsScriptsWithUnknownType() {
        $html = '<script type="text/template">foo</script>';
        $processed = $this->processor->__invoke($html);
        $this->assertSame($html, $processed);
    }

    public function dataAutoInclusionOfActionScripts_WithoutChildScripts() {
        yield [
            ['foo' => 'bar'],
        ];
        yield [
            new ArrayObject(['foo' => 'bar']),
        ];
    }

    /**
     * @dataProvider dataAutoInclusionOfActionScripts_WithoutChildScripts
     */
    public function testAutoInclusionOfActionScripts_WithoutChildScripts($jsConf) {
        $request = $this->mkRequest('cat/tail');
        $request['jsConf'] = $jsConf;

        $processor = new ScriptProcessor($request, $this->mkSiteStub('some/blog'));

        $childPageHtml = <<<OUT
This
is a child
OUT;
        $processor->__invoke($childPageHtml);

        $parentScripts = '<script>before</script>
            <script src="/parent/script.js"></script>
            <script>after</script>';

        $html = $processor->__invoke('<body>' . $parentScripts . '</body>');

        $re = $this->quotedRe(
            [
                '<body>',
                '<script>before</script>',
                '<script src="' . $this->baseUriPath . '/parent/script.js"></script>',
                '<script>after</script>',
                '<script src="' . $this->baseUriPath . '/blog/lib/app/cat/tail.js"></script>',
                '<script>',
                'define(["require", "exports", "blog/lib/app/cat/tail"], function (require, exports, module) {',
                'module.main(window.app || {}, ' . json_encode((array) $jsConf, JSON_UNESCAPED_SLASHES) . ');',
                '});',
                '</script>',
                '</body>',
            ]
        );
        $this->assertMatchesRegularExpression($re, $html);
    }

    public function testAutoInclusionOfActionScripts_WithChildScripts() {
        $request = $this->mkRequest('cat/tail');

        $processor = new ScriptProcessor($request, $this->mkSiteStub('some/blog'));

        $childPage = <<<OUT
This
<script src="/foo/first.js"></script>
is
<script>
alert("OK");
</script>
a
<script src="bar/second.js"></script>
child
OUT;

        $processor->__invoke($childPage);

        $parentScripts = '<script>before</script>
            <script src="/parent/script.js"></script>
            <script>after</script>';

        $html = $processor->__invoke('<body>' . $parentScripts . '</body>');

        $re = $this->quotedRe(
            [
                '<body>',
                '<script>before</script>',
                '<script src="' . $this->baseUriPath . '/parent/script.js"></script>',
                '<script>after</script>',
                '<script src="' . $this->baseUriPath . '/foo/first.js"></script>',
                '<script>',
                'alert("OK");',
                '</script>',
                '<script src="bar/second.js"></script>',
                '</body>',
            ]
        );
        $this->assertMatchesRegularExpression($re, $html);
    }

    private function mkRequest(string $view): IRequest {
        $request = new Request(['view' => $view]);
        $uri = new Uri('http://localhost' . $this->baseUriPath . '?one=123');
        $uri->path()->setBasePath($this->baseUriPath);
        $request->setUri($uri);
        return $request;
    }
}
