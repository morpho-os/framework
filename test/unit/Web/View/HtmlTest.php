<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace MorphoTest\Unit\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\Html;

class HtmlTest extends TestCase {
    public function testEncodeDecode_OnlySpecialChars() {
        // $specialChars taken from Zend\Escaper\EscaperTest:
        $specialChars = [
            '\'' => '&#039;',
            '"'  => '&quot;',
            '<'  => '&lt;',
            '>'  => '&gt;',
            '&'  => '&amp;',
        ];
        foreach ($specialChars as $char => $expected) {
            $encoded = Html::encode($char);
            $this->assertSame($expected, $encoded);
            $this->assertSame($char, Html::decode($encoded));
        }
    }

    public function testEncodeDecode_SpecialCharsWithText() {
        $original = '<h1>Hello</h1>';
        $encoded = Html::encode($original);
        $this->assertEquals('&lt;h1&gt;Hello&lt;/h1&gt;', $encoded);
        $this->assertEquals($original, Html::decode($encoded));
    }
    
    public function testEmptyAttributes() {
        $this->assertEquals('', Html::attributes([]));
    }

    public function testMultipleAttributes() {
        $this->assertEquals(
            ' data-api name="foo" id="some-id"',
            Html::attributes(['data-api', 'name' => 'foo', 'id' => 'some-id'])
        );
    }

    public function testSingleTag() {
        $attributes = ['href' => 'foo/bar.css', 'rel' => 'stylesheet'];
        $expected = '<link href="foo/bar.css" rel="stylesheet">';
        $this->assertEquals(
            $expected,
            Html::tag('link', $attributes, null, ['eol' => false, 'isSingle' => true])
        );
        $this->assertEquals(
            $expected,
            Html::singleTag('link', $attributes, ['eol' => false])
        );
    }

    public function testSingleTag_WithXmlOption() {
        $attributes = ['bar' => 'test'];
        $expected = '<foo bar="test" />';
        $this->assertEquals(
            $expected,
            Html::tag('foo', $attributes, null, ['isXml' => true, 'eol' => false, 'isSingle' => true])
        );
        $this->assertEquals(
            $expected,
            Html::singleTag('foo', $attributes, ['isXml' => true, 'eol' => false])
        );
    }

    public function testTag() {
        $attributes = ['href' => 'foo/bar'];
        $options = ['eol' => false];
        $this->assertEquals('<a href="foo/bar">Hello</a>', Html::tag('a', $attributes, 'Hello', $options));
    }

    public function testTag_WithEol() {
        $this->assertEquals("<foo></foo>\n", Html::tag('foo', [], null));
        $this->assertEquals("<foo></foo>\n", Html::tag('foo', [], null, ['eol' => true]));
    }

    public function testTag_WithEscapeTextOption() {
        $this->assertEquals('<foo>&quot;</foo>', Html::tag('foo', [], '"', ['eol' => false, 'escapeText' => true]));
        $this->assertEquals('<foo>&quot;</foo>', Html::tag('foo', [], '"', ['eol' => false]));
        $this->assertEquals('<foo>"</foo>', Html::tag('foo', [], '"', ['eol' => false, 'escapeText' => false]));
    }
}
