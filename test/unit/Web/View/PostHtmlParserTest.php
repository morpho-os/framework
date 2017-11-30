<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace MorphoTest\Unit\Web\View;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Core\ModuleIndex;
use Morpho\Core\ModuleMeta;
use Morpho\Web\Request;
use Morpho\Web\Site;
use Morpho\Web\View\PostHtmlParser;

class PostHtmlParserTest extends TestCase {
    private $parser;

    public function setUp() {
        $serviceManager = $this->newConfiguredServiceManager(['foo/bar', 'Module', 'cache']);
        $this->parser = new PostHtmlParser($serviceManager);
    }

    public function testHandlingOfScripts_InChildParentPages() {
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

        $this->assertRegExp('~^<body>\s+This is a\s+parent\s*<script src="bar/parent.js"></script>\s*<script src="foo/child.js"></script>\s*</body>$~', $html);
    }

    public function testHandlingOfScripts_IndexAttribute() {
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

        $this->assertRegExp('~^<body>\s+This is a\s+parent\s*<script src="foo/child.js"></script>\s*<script src="bar/parent.js"></script>\s*</body>$~', $html);
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
        $serviceManager = $this->newConfiguredServiceManager(['table', 'cat', 'tail']);
        $parser = new PostHtmlParser($serviceManager);

        $childPage = <<<OUT
This
<script src="foo/first.js"></script>
<script src="bar/second.js"></script>
is a child
OUT;
        $parser->__invoke($childPage);
        $this->assertRegExp(
            '~^<body>\s*<script src="foo/first.js"></script>\s*<script src="bar/second.js"></script>\s*<script src="module/table/app/cat/tail.js"></script>\s*<script>\s*\$\(function \(\) \{\s*define\(\["require", "exports", "table/app/cat/tail"\], function \(require, exports, module\) \{\s*module\.main\(\);\s*\}\);\s*\}\);\s*</script>\s*</body>$~s',
            $parser->__invoke('<body></body>')
        );
    }

    public function testAutoInclusionOfActionScripts_WithChildPageInlineScript() {
        $serviceManager = $this->newConfiguredServiceManager(['table', 'cat', 'tail']);
        $parser = new PostHtmlParser($serviceManager);

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
            '~^<body>\s*<script src="foo/first.js"></script>\s*<script src="bar/second.js"></script>\s*<script src="module/table/app/cat/tail.js"></script>\s*<script>\s*alert\("OK"\);\s*</script>\s*</body>$~s',
            $parser->__invoke('<body></body>')
        );
    }

    private function newConfiguredServiceManager($handler) {
        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('handler')
            ->willReturn($handler);

        $siteModuleName = 'random/example';
        $site = $this->createConfiguredMock(Site::class, [
            'moduleName' => $siteModuleName,
        ]);
        $publicDirPath = $this->getTestDirPath();
        $moduleMeta = $this->createConfiguredMock(ModuleMeta::class, ['publicDirPath' => $publicDirPath]);
        $moduleIndex = $this->createMock(ModuleIndex::class);
        $moduleIndex->expects($this->any())
            ->method('moduleMeta')
            ->with($siteModuleName)
            ->willReturn($moduleMeta);
        $services = [
            'request' => $request,
            'site' => $site,
            'moduleIndex' => $moduleIndex,
        ];
        $serviceManager = new ServiceManager($services);
        return $serviceManager;
    }
}