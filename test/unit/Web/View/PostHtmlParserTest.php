<?php
declare(strict_types=1);
namespace MorphoTest\Unit\Web\View;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\View\PostHtmlParser;

class PostHtmlParserTest extends TestCase {
    private $parser;

    public function setUp() {
        $serviceManager = $this->newServiceManager($this->newRequest(), $this->newSite());
        $this->parser = new PostHtmlParser($serviceManager);
    }

    public function testDefaultHandlingOfChildParentPages() {
        $childPage = <<<OUT
This
<script src="foo/child.js"></script>
is a child
OUT;

        // Parser should save child scripts
        $this->assertRegExp('~^This\s+is a child$~', $this->parser->__invoke($childPage));

        $parentPage = <<<OUT
<body>
This is a
<script src="bar/parent.js"></script>
parent
</body>
OUT;
        // And now render them for <body>
        $html = $this->parser->__invoke($parentPage);

        $this->assertRegExp('~^<body>\s+This is a\s+parent\s*<script src="/base/path/bar/parent.js"></script>\s*<script src="/base/path/foo/child.js"></script>\s*</body>$~', $html);
    }

    public function testIndexAttrRenderParentPageScriptsAfterChildPageScripts() {
        $childPage = <<<OUT
This
<script src="foo/child.js"></script>
is a child
OUT;

        // Parser should save child scripts
        $this->parser->__invoke($childPage);

        $parentPage = <<<OUT
<body>
This is a
<script src="bar/parent.js" _index="100"></script>
parent
</body>
OUT;
        // And now render them for <body>
        $html = $this->parser->__invoke($parentPage);

        $this->assertRegExp('~^<body>\s+This is a\s+parent\s*<script src="/base/path/foo/child.js"></script>\s*<script src="/base/path/bar/parent.js"></script>\s*</body>$~', $html);
    }

    public function dataForSkipAttr() {
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
     * @dataProvider dataForSkipAttr
     */
    public function testSkipAttr($tag) {
        $parser = new class ($this->createMock(ServiceManager::class)) extends PostHtmlParser {
            protected function containerBody($tag) {
                $res = parent::containerBody($tag);
                if (null !== $res) {
                    throw new \RuntimeException("The tag must be skipped");
                }
            }
            protected function containerScript($tag) {
                $res = parent::containerScript($tag);
                if (null !== $res) {
                    throw new \RuntimeException("The tag must be skipped");
                }
            }
        };

        $html = '<' . $tag . ' _skip></' . $tag . '>';
        $this->assertSame($html, $parser->__invoke($html));
    }

    public function testAutoInclusionOfActionScripts_WithoutChildPageInlineScript() {
        $parser = $this->newParserForAutoInclusionTest();

        $childPage = <<<OUT
This
<script src="foo/first.js"></script>
<script src="bar/second.js"></script>
is a child
OUT;
        $parser->__invoke($childPage);
        $this->assertRegExp(
            '~^<body>\s*<script src="/base/path/foo/first.js"></script>\s*<script src="/base/path/bar/second.js"></script>\s*<script src="/base/path/module/table/app/cat/tail.js"></script>\s*<script>\s*\$\(function \(\) \{\s*define\(\["require", "exports", "table/app/cat/tail"\], function \(require, exports, module\) \{\s*module\.main\(\);\s*\}\);\s*\}\);\s*</script>\s*</body>$~s',
            $parser->__invoke('<body></body>')
        );
    }

    public function testAutoInclusionOfActionScripts_WithChildPageInlineScript() {
        $parser = $this->newParserForAutoInclusionTest();

        $childPage = <<<OUT
This
<script src="foo/first.js"></script>
<script>
alert("OK");
</script>
<script src="bar/second.js"></script>
is a child
OUT;
        $parser->__invoke($childPage);
        $this->assertRegExp(
            '~^<body>\s*<script src="/base/path/foo/first.js"></script>\s*<script src="/base/path/bar/second.js"></script>\s*<script src="/base/path/module/table/app/cat/tail.js"></script>\s*<script>\s*alert\("OK"\);\s*</script>\s*</body>$~s',
            $parser->__invoke('<body></body>')
        );
    }

    private function newRequest() {
        return new class {
            public function uri() {
                return new class {
                    public function prependWithBasePath(string $uri) {
                        return '/base/path/' . $uri;
                    }
                };
            }

            public function handler() {
                return ['country', 'state', 'city'];
            }
        };
    }

    private function newSite() {
        return new class($this->getTestDirPath()) {
            private $publicDirPath;
            public function __construct($publicDirPath) {
                $this->publicDirPath = $publicDirPath;
            }
            public function publicDirPath() {
                return $this->publicDirPath;
            }
        };
    }

    private function newServiceManager($request, $site) {
        $serviceManager = new ServiceManager();
        $serviceManager->set('request', $request);
        $serviceManager->set('site', $site);
        return $serviceManager;
    }

    private function newParserForAutoInclusionTest() {
        $request = new class {
            public function uri() {
                return new class {
                    public function prependWithBasePath(string $uri) {
                        return '/base/path/' . $uri;
                    }
                };
            }

            public function handler() {
                return ['table', 'cat', 'tail'];
            }
        };
        return new PostHtmlParser($this->newServiceManager($request, $this->newSite()));
    }
}