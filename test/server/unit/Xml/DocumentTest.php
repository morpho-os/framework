<?php declare(strict_types=1);
namespace MorphoTest\Xml;

use Morpho\Test\TestCase;
use Morpho\Xml\Document;

class DocumentTest extends TestCase {
    public function testSelect() {
        $html = <<<OUT
<div>
    <ol>
        <li>One</li>
        <li>Two</li>
        <li>Three</li>
    </ol>
</div>
OUT;
        $doc = Document::fromString($html);
        $nodes = $doc->select('//li');
        $this->assertEquals(3, count($nodes));
        $this->assertEquals('One', $nodes->item(0)->nodeValue);
        $this->assertEquals('Two', $nodes->item(1)->nodeValue);
        $this->assertEquals('Three', $nodes->item(2)->nodeValue);
        $i = 0;
        foreach ($nodes as $node) {
            switch ($i) {
                case 0:
                    $this->assertEquals('One', $node->nodeValue);
                    break;
                case 1:
                    $this->assertEquals('Two', $node->nodeValue);
                    break;
                case 2:
                    $this->assertEquals('Three', $node->nodeValue);
                    break;
            }
            $i++;
        }
        $this->assertEquals(3, $i);
    }

    public function testCreate_ThrowsExceptionOnInvalidOptions() {
        $this->expectException(\Morpho\Base\InvalidOptionsException::class, "Invalid options: invalidOne, invalidTwo");
        Document::create(['encoding' => 'utf-8', 'invalidOne' => 'first', 'invalidTwo' => 'second']);
    }

    public function testFromString_ThrowsExceptionOnInvalidOptions() {
        $this->expectException(\Morpho\Base\InvalidOptionsException::class, "Invalid options: invalidOne, invalidTwo");
        Document::fromString("foo", ['encoding' => 'utf-8', 'invalidOne' => 'first', 'invalidTwo' => 'second']);
    }

    public function testFromString_FixEncodingOption() {
        $html = <<<OUT
<!DOCTYPE html><html><body>µ</body></html>
OUT;
        $doc = Document::fromString($html, ['fixEncoding' => true, 'formatOutput' => false]);
        $this->assertHtmlEquals(<<<OUT
<!DOCTYPE html>
<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>µ</body></html>
OUT
            , $doc->saveHTML()
        );

        $doc = Document::fromString($html, ['fixEncoding' => false, 'formatOutput' => false]);
        $this->assertHtmlEquals(<<<OUT
<!DOCTYPE html>
<html><body>&Acirc;&micro;</body></html>
OUT
            , $doc->saveHTML()
        );
    }
}
