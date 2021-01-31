<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use ArrayObject;
use Morpho\App\ISite;
use Morpho\Testing\TestCase;
use Morpho\App\Web\View\ScriptProcessor;
use RuntimeException;
use const Morpho\App\FRONTEND_MODULE_DIR_NAME;

class ScriptProcessorTest extends TestCase {
    private ScriptProcessor $processor;
    private string $baseUriPath;

    public function setUp(): void {
        parent::setUp();
        $this->baseUriPath = '/base/path';
        $this->processor = new ScriptProcessor($this->mkRequestStub('foo/bar'), $this->mkSiteStub('abc/efg'));
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

        $re = $this->quotedRe([
            '<body>',
            'This is a',
            'parent',
            '<script src="' . $this->baseUriPath . '/bar/parent.js"></script>',
            '<script src="' . $this->baseUriPath . '/foo/child.js"></script>',
            '</body>',
        ]);

        $this->assertMatchesRegularExpression($re, $html);
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

        $re = $this->quotedRe([
            '<body>',
            'This is a',
            'parent',
            '<script src="foo/child.js"></script>',
            '<script src="bar/parent.js"></script>',
            '</body>'
        ]);
        $this->assertMatchesRegularExpression($re, $html);
    }

    public function dataForSkipAttribute() {
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
     * @dataProvider dataForSkipAttribute
     */
    public function testSkipAttribute($tag) {
        $processor = new class ($this->mkRequestStub('foo'), $this->mkSiteStub('abc/efg')) extends ScriptProcessor {
            protected function containerBody($tag) {
                $res = parent::containerBody($tag);
                if (isset($res['_skip'])) {
                    throw new RuntimeException("The _skip attribute must be removed");
                }
                return $res;
            }

            protected function containerScript($tag) {
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

    public function dataForAutoInclusionOfActionScripts_WithoutChildPageInlineScript() {
        yield [
            ['foo' => 'bar'],
        ];
        yield [
            new ArrayObject(['foo' => 'bar']),
        ];
    }

    /**
     * @dataProvider dataForAutoInclusionOfActionScripts_WithoutChildPageInlineScript
     */
    public function testAutoInclusionOfActionScripts_WithoutChildPageInlineScript($jsConf) {
        $request = $this->mkRequestStub('cat/tail');
        $request['jsConf'] = $jsConf;

        $processor = new ScriptProcessor($request, $this->mkSiteStub('some/blog'));

        $childPageHtml = <<<OUT
This
<script src="/foo/first.js"></script>
<script src="bar/second.js"></script>
is a child
OUT;
        $processor->__invoke($childPageHtml);

        $html = $processor->__invoke('<body></body>');

        $re = $this->quotedRe([
                '<body>',
                '<script src="' . $this->baseUriPath . '/foo/first.js"></script>',
                '<script src="bar/second.js"></script>',
                '<script src="' . $this->baseUriPath . '/blog/app/cat/tail.js"></script>',
                '<script>',
                'define(["require", "exports", "blog/app/cat/tail"], function (require, exports, module) {',
                'module.main(window.app || {}, ' . json_encode((array)$jsConf, JSON_UNESCAPED_SLASHES) . ');',
                '});',
                '</script>',
                '</body>',
            ]);
        $this->assertMatchesRegularExpression($re, $html);
    }

    public function testAutoInclusionOfActionScripts_WithChildPageInlineScript() {
        $request = $this->mkRequestStub('cat/tail');

        $processor = new ScriptProcessor($request, $this->mkSiteStub('some/blog'));

        $childPage = <<<OUT
This
<script src="/foo/first.js"></script>
<script>
alert("OK");
</script>
<script src="bar/second.js"></script>
is a child
OUT;

        $processor->__invoke($childPage);
        $html = $processor->__invoke('<body></body>');

        $re = $this->quotedRe([
            '<body>',
            '<script src="' . $this->baseUriPath . '/foo/first.js"></script>',
            '<script src="bar/second.js"></script>',
            '<script src="' . $this->baseUriPath . '/blog/app/cat/tail.js"></script>',
            '<script>',
            'alert("OK");',
            '</script>',
            '</body>',
        ]);
        $this->assertMatchesRegularExpression($re, $html);
    }

    private function quotedRe(array $parts): string {
        return '~^' . implode('\s*?', array_map(fn ($s) => preg_quote($s), $parts)) . '$~s';
    }

    private function mkRequestStub(string $view) {
        return new class (['view' => $view], $this->baseUriPath) extends ArrayObject {
            private $baseUriPath;
            public function __construct($array, $baseUriPath) {
                parent::__construct($array);
                $this->baseUriPath = $baseUriPath;
            }

            public function prependUriWithBasePath($uri) {
                $mkUri = function ($uri) {
                    return new class ($uri) {
                        private $uri;
                        public function __construct($uri) {
                            $this->uri = $uri;
                        }

                        public function toStr() {
                            return $this->uri;
                        }
                    };
                };
                if (strlen($uri) > 0) {
                    if ($uri[0] === '/') {
                        return $mkUri(rtrim($this->baseUriPath . $uri, '/'));
                    }
                }
                return $mkUri($uri);
            }
        };
    }

    private function mkSiteStub(string $siteModuleName): ISite {
        $site = $this->createStub(ISite::class);
        $site->method('moduleName')
            ->willReturn($siteModuleName);
        $site->method('conf')
            ->willReturn([
                'paths' => [
                    'frontendModuleDirPath' => $this->getTestDirPath() . '/' . FRONTEND_MODULE_DIR_NAME,
                    'baseUriPath' => $this->baseUriPath,
                ],
            ]);
        return $site;
    }
}
