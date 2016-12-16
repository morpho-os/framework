<?php
namespace Morpho\Xml;

use Countable;

class XPathResult implements \Iterator, Countable {
    protected $nodeList;

    protected $offset = 0;

    private $count;

    public function __construct(\DOMNodeList $nodeList) {
        $this->nodeList = $nodeList;
    }

    public function toHtml(array $options = null): string {
        $doc = Document::create($options);
        foreach ($this->nodeList as $node) {
            $doc->appendChild($doc->importNode($node, true));
        }
        return $doc->saveHTML();
    }

    public function item($offset) {
        return $this->nodeList->item($offset);
    }
    
    public function current() {
        return $this->item($this->offset);
    }

    public function next()/*: void */ {
        $this->offset++;
    }

    public function key() {
        return $this->offset;
    }

    public function rewind()/*: void */ {
        $this->offset = 0;
    }

    public function valid(): bool {
        return (bool)$this->item($this->offset);
    }

    public function count(): int {
        if (null === $this->count) {
            $i = 0;
            foreach ($this->nodeList as $node) {
                $i++;
            }
            $this->count = $i;
        }
        return $this->count;
    }
}
