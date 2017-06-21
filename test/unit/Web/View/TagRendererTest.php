<?php declare(strict_types=1);
namespace MorphoTest\Unit\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\TagRenderer;
use Morpho\Web\View\Doctype;

class TagRendererTest extends TestCase {
    public function testRenderEmptyAttributes() {
        $this->assertEquals('', TagRenderer::attributes([]));
    }

    public function testRenderMultipleAttributes() {
        $this->assertEquals(
            ' data-api name="foo" id="some-id"',
            TagRenderer::attributes(['data-api', 'name' => 'foo', 'id' => 'some-id'])
        );
    }

    public function testRenderSingle() {
        $attributes = ['href' => 'foo/bar.css', 'rel' => 'stylesheet'];
        $expected = '<link href="foo/bar.css" rel="stylesheet">';
        $this->assertEquals(
            $expected,
            TagRenderer::render('link', $attributes, null, ['eol' => false, 'isSingle' => true])
        );
        $this->assertEquals(
            $expected,
            TagRenderer::renderSingle('link', $attributes, ['eol' => false])
        );
    }

    public function testRenderSingleWithXmlOption() {
        $attributes = ['bar' => 'test'];
        $expected = '<foo bar="test" />';
        $this->assertEquals(
            $expected,
            TagRenderer::render('foo', $attributes, null, ['isXml' => true, 'eol' => false, 'isSingle' => true])
        );
        $this->assertEquals(
            $expected,
            TagRenderer::renderSingle('foo', $attributes, ['isXml' => true, 'eol' => false])
        );
    }

    public function testRender() {
        $attributes = ['href' => 'foo/bar'];
        $options = ['eol' => false];
        $this->assertEquals('<a href="foo/bar">Hello</a>', TagRenderer::render('a', $attributes, 'Hello', $options));
    }

    public function testRenderWithEol() {
        $this->assertEquals("<foo></foo>\n", TagRenderer::render('foo', [], null));
        $this->assertEquals("<foo></foo>\n", TagRenderer::render('foo', [], null, ['eol' => true]));
    }

    public function testRenderWithEscapeTextOption() {
        $this->assertEquals('<foo>&quot;</foo>', TagRenderer::render('foo', [], '"', ['eol' => false, 'escapeText' => true]));
        $this->assertEquals('<foo>&quot;</foo>', TagRenderer::render('foo', [], '"', ['eol' => false]));
        $this->assertEquals('<foo>"</foo>', TagRenderer::render('foo', [], '"', ['eol' => false, 'escapeText' => false]));
    }
}
