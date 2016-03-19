<?php
namespace System\View\Plugin;

use Morpho\Test\TestCase;

class DirHierarchyRendererPluginTest extends TestCase {
    public function dataForRender() {
        return [
            [
                [
                    'a',
                    ['b'],
                    'c',
                ],
                 '<ul class="fs-tree"><li class="file">a</li><li class="dir">b</li><li class="file">c</li></ul>',
            ],
            [
                [
                    'a level 0', // file
                    ['b level 0', [ // not empty dir
                        'b level 0/a level 1',
                        ['b level 0/b level 1', [// not empty dir
                            ['b level 0/b level 1/a level 2', [ // not empty dir
                                'b level 0/b level 1/a level 2/a level 3',  // file
                                ['b level 0/b level 1/a level 2/b level 3'] // empty dir
                            ]],
                            'b level 0/b level 1/b level 2', // file
                        ]],
                        'b level 0/c level 1', // file
                    ]],
                    ['c level 0'], // empty dir
                ],
                '<ul class="fs-tree">
                    <li class="file">a level 0</li>
                    <li class="dir">b level 0
                        <ul>
                            <li class="file">b level 0/a level 1</li>
                            <li class="dir">b level 0/b level 1
                                <ul>
                                    <li class="dir">b level 0/b level 1/a level 2
                                        <ul>
                                            <li class="file">b level 0/b level 1/a level 2/a level 3</li>
                                            <li class="dir">b level 0/b level 1/a level 2/b level 3</li>
                                        </ul>
                                    </li>
                                    <li class="file">b level 0/b level 1/b level 2</li>
                                </ul>
                            </li>
                            <li class="file">b level 0/c level 1</li>
                        </ul>
                    </li>
                    <li class="dir">c level 0</li>
                </ul>',
            ],
        ];
    }

    /**
     * @dataProvider dataForRender
     */
    public function testRender($fsEntries, $expected) {
        $dirHierRenderer = (new DirHierarchyRendererPlugin());
        $internalNodeRenderer = function ($name, string $renderedChildren) {
            return '<li class="dir">' . $name . $renderedChildren . '</li>';
        };
        $leafNodeRenderer = function ($html) {
            return '<li class="file">' . $html . '</li>';
        };
        $dirHierRenderer = $dirHierRenderer
            ->setInternalNodeRenderer($internalNodeRenderer)
            ->setLeafNodeRenderer($leafNodeRenderer);
        $this->assertHtmlEquals($expected, $dirHierRenderer->render($fsEntries));
    }
}