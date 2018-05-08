<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\Ioc\IServiceManager;
use Morpho\Ioc\ServiceManager;
use Morpho\Testing\TestCase;
use Morpho\App\Core\ModuleIndex;
use Morpho\App\Core\ModuleMeta;
use Morpho\App\Web\Request;
use Morpho\App\Web\Site;
use Morpho\App\Web\View\ScriptProcessor;

class ScriptProcessorTest extends TestCase {
    /**
     * @var ScriptProcessor
     */
    private $processor;

    public function setUp() {
        parent::setUp();
        $serviceManager = $this->newConfiguredServiceManager(['foo/bar', 'Module', 'cache']);
        $this->processor = new ScriptProcessor($serviceManager);
    }

    public function testHandlingOfScripts_InChildParentPages() {
        $childPage = <<<OUT
This
<script src="foo/child.js"></script>
is a child
OUT;

        // processor should save child scripts
        $this->assertRegExp('~^This\s+is a child$~', $this->processor->__invoke($childPage));

        $parentPage = <<<OUT
<body>
This is a
<script src="bar/parent.js"></script>
parent
</body>
OUT;
        // And now render them for <body>
        $html = $this->processor->__invoke($parentPage);

        $this->assertRegExp('~^<body>\s+This is a\s+parent\s*<script src="bar/parent.js"></script>\s*<script src="foo/child.js"></script>\s*</body>$~', $html);
    }

    public function testHandlingOfScripts_IndexAttribute() {
        $childPage = <<<OUT
This
<script src="foo/child.js"></script>
is a child
OUT;

        // processor should save child scripts
        $this->processor->__invoke($childPage);

        $parentPage = <<<OUT
<body>
This is a
<script src="bar/parent.js" _index="100"></script>
parent
</body>
OUT;
        // And now render them for <body>
        $html = $this->processor->__invoke($parentPage);

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
        $processor = new class ($this->createMock(ServiceManager::class)) extends ScriptProcessor {
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
        $this->assertSame($html, $processor->__invoke($html));
    }

    public function testSkipsScriptsWithUnknownType() {
        $html = '<script type="text/template">foo</script>';
        $serviceManager = $this->createMock(IServiceManager::class);
        $processor = new ScriptProcessor($serviceManager);

        $processed = $processor->__invoke($html);
        $this->assertSame($html, $processed);
    }

    public function dataForAutoInclusionOfActionScripts_WithoutChildPageInlineScript() {
        yield [
            ['foo' => 'bar'],
        ];
        yield [
            new \ArrayObject(['foo' => 'bar']),
        ];
    }

    /**
     * @dataProvider dataForAutoInclusionOfActionScripts_WithoutChildPageInlineScript
     */
    public function testAutoInclusionOfActionScripts_WithoutChildPageInlineScript($jsConfig) {
        $serviceManager = $this->newConfiguredServiceManager(['table', 'cat', 'tail']);
        $request = $serviceManager['request'];
        $request['jsConfig'] = $jsConfig;

        $processor = new ScriptProcessor($serviceManager);

        $childPageHtml = <<<OUT
This
<script src="foo/first.js"></script>
<script src="bar/second.js"></script>
is a child
OUT;
        $processor->__invoke($childPageHtml);

        $processedBody = $processor->__invoke('<body></body>');

        $jsConfigStr = \json_encode((array)$jsConfig, JSON_UNESCAPED_SLASHES);
        $this->assertRegExp(
            '~^<body>\s*<script src="foo/first.js"></script>\s*<script src="bar/second.js"></script>\s*<script src="module/table/app/cat/tail.js"></script>\s*<script>\s*define\(\["require", "exports", "table/app/cat/tail"\], function \(require, exports, module\) \{\s*module\.main\(' . \preg_quote($jsConfigStr, '~') . '\);\s*\}\);\s*</script>\s*</body>$~s',
            $processedBody
        );
    }

    public function testAutoInclusionOfActionScripts_WithChildPageInlineScript() {
        $serviceManager = $this->newConfiguredServiceManager(['table', 'cat', 'tail']);
        $processor = new ScriptProcessor($serviceManager);

        $childPage = <<<OUT
This
<script src="foo/first.js"></script>
<script>
alert("OK");
</script>
<script src="bar/second.js"></script>
is a child
OUT;
        $processor->__invoke($childPage);
        $this->assertRegExp(
            '~^<body>\s*<script src="foo/first.js"></script>\s*<script src="bar/second.js"></script>\s*<script src="module/table/app/cat/tail.js"></script>\s*<script>\s*alert\("OK"\);\s*</script>\s*</body>$~s',
            $processor->__invoke('<body></body>')
        );
    }

    private function newConfiguredServiceManager($handler) {
        $request = new Request();
        $request->setHandler($handler);

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
