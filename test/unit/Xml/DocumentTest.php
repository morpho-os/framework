<?php declare(strict_types=1);
namespace MorphoTest\Unit\Xml;

use Countable;
use Iterator;
use Morpho\Base\InvalidOptionsException;
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
        $this->assertSame(3, count($nodes));
        $this->assertSame('One', $nodes->item(0)->nodeValue);
        $this->assertSame('Two', $nodes->item(1)->nodeValue);
        $this->assertSame('Three', $nodes->item(2)->nodeValue);
        $i = 0;
        foreach ($nodes as $node) {
            switch ($i) {
                case 0:
                    $this->assertSame('One', $node->nodeValue);
                    break;
                case 1:
                    $this->assertSame('Two', $node->nodeValue);
                    break;
                case 2:
                    $this->assertSame('Three', $node->nodeValue);
                    break;
            }
            $i++;
        }
        $this->assertSame(3, $i);
        $this->assertSame(3, $nodes->count());

        $this->assertInstanceOf(Countable::class, $nodes);
        $this->assertInstanceOf(Iterator::class, $nodes);
        
        $this->assertSame($nodes->item(0), $nodes->head());
        $this->assertSame([$nodes->item(1), $nodes->item(2)], $nodes->tail());
        $this->assertSame($nodes->item(2), $nodes->last());
        $this->assertSame([$nodes->item(0), $nodes->item(1)], $nodes->init());
    }

    public function testNew_ThrowsExceptionOnInvalidOptions() {
        $this->expectException(InvalidOptionsException::class, "Invalid options: invalidOne, invalidTwo");
        Document::new(['encoding' => 'utf-8', 'invalidOne' => 'first', 'invalidTwo' => 'second']);
    }

    public function testNew_DefaultOptions() {
        $doc = Document::new();
        $this->assertInstanceOf(Document::class, $doc);
        $doc1 = Document::new();
        $this->assertInstanceOf(Document::class, $doc1);
        $this->assertNotSame($doc, $doc1);
    }

    public function testFromString_ThrowsExceptionOnInvalidOptions() {
        $this->expectException(InvalidOptionsException::class, "Invalid options: invalidOne, invalidTwo");
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
