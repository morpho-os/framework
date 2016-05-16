<?php
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
}
