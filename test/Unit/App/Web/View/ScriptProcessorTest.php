<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\App\ClientModule;
use Morpho\App\ServerModule;
use Morpho\Ioc\IServiceManager;
use Morpho\Ioc\ServiceManager;
use Morpho\Testing\TestCase;
use Morpho\App\ModuleIndex;
use Morpho\App\Web\Request;
use Morpho\App\Site;
use Morpho\App\Web\View\ScriptProcessor;
use const Morpho\App\CLIENT_MODULE_DIR_NAME;

class ScriptProcessorTest extends TestCase {
    private ScriptProcessor $processor;

    public function setUp(): void {
        parent::setUp();
        $handler = [
            'modulePath' => 'bar',
            'controllerPath' => 'foo',
            'method' => 'child',
        ];
        $serviceManager = $this->mkConfiguredServiceManager($handler);
        $this->processor = new ScriptProcessor($serviceManager);
    }

    public function testHandlingOfScripts_InChildParentPages() {
        $childPage = <<<OUT
This
<script src="foo/child.js"></script>
is a child
OUT;

        // processor should save child scripts
        $this->assertMatchesRegularExpression('~^This\\s+is a child$~', $this->processor->__invoke($childPage));

        $parentPage = <<<OUT
<body>
This is a
<script src="bar/parent.js"></script>
parent
</body>
OUT;
        // And now render them for <body>
        $html = $this->processor->__invoke($parentPage);

        $this->assertMatchesRegularExpression('~^<body>\s+This is a\s+parent\s*<script src="bar/parent.js"></script>\s*<script src="foo/child.js"></script>\s*</body>$~', $html);
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

        $this->assertMatchesRegularExpression('~^<body>\s+This is a\s+parent\s*<script src="foo/child.js"></script>\s*<script src="bar/parent.js"></script>\s*</body>$~', $html);
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
                if (isset($res['_skip'])) {
                    throw new \RuntimeException("The _skip attribute must be removed");
                }
                return $res;
            }

            protected function containerScript($tag) {
                $res = parent::containerScript($tag);
                if (isset($res['_skip'])) {
                    throw new \RuntimeException("The _skip attribute must be removed");
                }
                return $res;
            }
        };

        $html = '<' . $tag . ' _skip></' . $tag . '>';
        $this->assertSame("<$tag></$tag>", $processor->__invoke($html));
    }

    public function testSkipsScriptsWithUnknownType() {
        $html = '<script type="text/template">foo</script>';
        $serviceManager = $this->createMock(IServiceManager::class);
        /** @noinspection PhpParamsInspection */
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
    public function testAutoInclusionOfActionScripts_WithoutChildPageInlineScript($jsConf) {
        $serviceManager = $this->mkConfiguredServiceManager([
            'modulePath' => 'blog',
            'controllerPath' => 'cat',
            'method' => 'tail',
        ]);
        $request = $serviceManager['request'];
        $request['jsConf'] = $jsConf;

        $processor = new ScriptProcessor($serviceManager);

        $childPageHtml = <<<OUT
This
<script src="foo/first.js"></script>
<script src="bar/second.js"></script>
is a child
OUT;
        $processor->__invoke($childPageHtml);

        $processedBody = $processor->__invoke('<body></body>');

        $jsConfStr = \json_encode((array)$jsConf, JSON_UNESCAPED_SLASHES);
        $this->assertMatchesRegularExpression(
            '~^<body>\s*<script src="foo/first.js"></script>\s*<script src="bar/second.js"></script>\s*<script src="/blog/app/cat/tail.js"></script>\s*<script>\s*define\(\["require", "exports", "blog/app/cat/tail"\], function \(require, exports, module\) \{\s*module\.main\(window.app || {}, ' . \preg_quote($jsConfStr, '~') . '\);\s*\}\);\s*</script>\s*</body>$~s',
            $processedBody
        );
    }

    public function testAutoInclusionOfActionScripts_WithChildPageInlineScript() {
        $serviceManager = $this->mkConfiguredServiceManager([
            'modulePath' => 'blog',
            'controllerPath' => 'cat',
            'method' => 'tail',
        ]);
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

        $this->assertMatchesRegularExpression(
            '~^<body>\s*<script src="foo/first.js"></script>\s*<script src="bar/second.js"></script>\s*<script src="/blog/app/cat/tail.js"></script>\s*<script>\s*alert\("OK"\);\s*</script>\s*</body>$~s',
            $processor->__invoke('<body></body>')
        );
    }

    private function mkConfiguredServiceManager($handler) {
        $request = new Request();
        $request->setHandler($handler);

        $shortSiteModuleName = 'example';
        $fullSiteModuleName = 'random/' . $shortSiteModuleName;
        $site = $this->createConfiguredMock(Site::class, [
            'moduleName' => $fullSiteModuleName,
        ]);

        $clientModuleDirPath = $this->getTestDirPath() . '/' . CLIENT_MODULE_DIR_NAME . '/' . $shortSiteModuleName;
        $clientModule = $this->createConfiguredMock(ClientModule::class, ['dirPath' => $clientModuleDirPath]);

        $module = $this->createConfiguredMock(ServerModule::class, ['clientModule' => $clientModule]);
        $moduleIndex = $this->createMock(ModuleIndex::class);
        $moduleIndex->expects($this->any())
            ->method('module')
            ->with($fullSiteModuleName)
            ->willReturn($module);
        $services = [
            'request' => $request,
            'site' => $site,
            'serverModuleIndex' => $moduleIndex,
        ];
        return new ServiceManager($services);
    }
}
