<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web\View;

use Morpho\App\Web\View\TreeRendererPlugin;
use Morpho\Testing\TestCase;

class TreeRendererPluginTest extends TestCase {
    public function dataRender() {
        return [
            [
                [],
                '',
            ],
            [
                [
                    ['label' => 'a'],
                    ['label' => 'b'],
                    ['label' => 'c'],
                ],
                '<ul class="tree"><li class="tree__node tree__node-leaf">a</li><li class="tree__node tree__node-leaf">b</li><li class="tree__node tree__node-leaf">c</li></ul>',
            ],
            [
                [
                    ['label' => 'a'],
                    [
                        'label' => 'b',
                        'nodes' => [
                            ['label' => 'b/1'],
                            [
                                'label' => 'b/2',
                                'nodes' => [
                                    [
                                        'label' => 'b/2/1',
                                        'nodes' => [
                                            ['label' => 'b/2/1/1'],
                                            ['label' => 'b/2/1/2'],
                                        ],
                                    ],
                                    ['label' => 'b/2/2'],
                                ],
                            ],
                            ['label' => 'b/3'],
                        ],
                    ],
                    ['label' => 'c'],
                ],
                '<ul class="tree">
                    <li class="tree__node tree__node-leaf">a</li>
                    <li class="tree__node tree__node-internal">b
                        <ul>
                            <li class="tree__node tree__node-leaf">b/1</li>
                            <li class="tree__node tree__node-internal">b/2
                                <ul>
                                    <li class="tree__node tree__node-internal">b/2/1
                                        <ul>
                                            <li class="tree__node tree__node-leaf">b/2/1/1</li>
                                            <li class="tree__node tree__node-leaf">b/2/1/2</li>
                                        </ul>
                                    </li>
                                    <li class="tree__node tree__node-leaf">b/2/2</li>
                                </ul>
                            </li>
                            <li class="tree__node tree__node-leaf">b/3</li>
                        </ul>
                    </li>
                    <li class="tree__node tree__node-leaf">c</li>
                </ul>',
            ],
        ];
    }

    /**
     * @dataProvider dataRender
     */
    public function testRender($hierarchy, $expected) {
        $renderer = (new TreeRendererPlugin());
        /*
        $internalNodeRenderer = function ($name, string $renderedChildren) {
            return '<li class="dir">' . $name . $renderedChildren . '</li>';
        };
        $leafNodeRenderer = function ($html) {
            return '<li class="file">' . $html . '</li>';
        };
        $dirHierRenderer = $dirHierRenderer
            ->setInternalNodeRenderer($internalNodeRenderer)
            ->setLeafNodeRenderer($leafNodeRenderer);
        */
        $this->assertHtmlEquals($expected, $renderer->render($hierarchy));
    }
}
