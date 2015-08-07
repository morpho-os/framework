<?php
namespace MorphoTest\Web\View;

use Morpho\Web\View\HtmlSemiParser;
use Morpho\Test\TestCase;

class HtmlSemiParserTest extends TestCase {
    public function setUp() {
        $this->parser = new HtmlSemiParser();
    }

    public function testCallsTagHandler() {
        $handler = $this->parser->attachHandler(new MyTagHandler());
        $html = <<<HTML
<body>
    <a href="/foo/bar" class="my">Some text</a>
</body>
HTML;
        $this->parser->filter($html);
        $this->assertEquals(['href' => '/foo/bar', 'class' => 'my'], $handler->getAttributes());
        $this->assertEquals('a', $handler->getTagName());
    }

    public function testCallContainerHandler() {
        $handler = $this->parser->attachHandler(new MyContainerHandler());
        $html = <<<HTML
<div class="my-class" style="width: 98%;">
    <a href="foo">123</a>
</div>
HTML;
        $this->parser->filter($html);
        $this->assertEquals('<a href="foo">123</a>', $handler->getText());
        $this->assertEquals(
            [
                'class' => 'my-class',
                'style' => 'width: 98%;',
            ],
            $handler->getAttributes()
        );
        $this->assertEquals('div', $handler->getTagName());
    }

    public function testSkipsTagHandlerIfNoTag() {
        $handler = $this->parser->attachHandler(new MyTagHandler());
        $html = <<<HTML
<body>
    <form action="/some/uri" method="post">
        <input name="some_name">
    </form>
</body>
HTML;
        $this->parser->filter($html);
        $this->assertNull($handler->getTagName());
        $this->assertEquals([], $handler->getAttributes());
    }

    public function testCanRemoveTag() {
        $handler = $this->parser->attachHandler(new RemoveTagHandler());
        $html = <<<HTML
<body>
<br>
<br>
</body>
HTML;
        $expected = <<<HTML
<body>
<br>
</body>
HTML;
        $this->assertEquals($expected, $this->parser->filter($html));
    }

    public function testCanRemoveContainer() {
        $handler = $this->parser->attachHandler(new RemoveTagHandler());
        $html = <<<HTML
<body>
<script src="template.dart"></script>
<script src="main.dart"></script>
</body>
HTML;
        $expected = <<<HTML
<body>
<script src="main.dart"></script>
</body>
HTML;
        $this->assertEquals($expected, $this->parser->filter($html));
    }
}

abstract class TagHandler {
    protected $tag = [];

    public function getTagName() {
        return isset($this->tag['_tagName']) ? $this->tag['_tagName'] : null;
    }

    public function getAttributes() {
        $attribs = [];
        foreach ($this->tag as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }
            $attribs[$key] = $value;
        }
        return $attribs;
    }
}

class MyContainerHandler extends TagHandler {
    public function containerDiv($tag) {
        $this->tag = $tag;
    }

    public function getText() {
        return trim($this->tag['_text']);
    }
}

class MyTagHandler extends TagHandler {
    public function tagA($tag) {
        $this->tag = $tag;
    }
}

class RemoveTagHandler {
    private $remove = false;

    public function tagBr($tag) {
        if ($this->remove) {
            return false;
        }
        $this->remove = true;
    }

    public function containerScript($tag) {
        if ($tag['src'] == 'template.dart') {
            return false;
        }
    }
}
