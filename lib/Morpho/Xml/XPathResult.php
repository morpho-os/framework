<?php
namespace Morpho\Xml;

class XPathResult implements \Iterator {
    protected $nodeList;

    protected $offset = 0;

    public function __construct(\DOMNodeList $nodeList) {
        $this->nodeList = $nodeList;
    }

    public function toHtml() {
        $doc = Document::create();
        $root = $doc->appendChild($doc->createElement('nodes'));
        foreach ($this->nodeList as $node) {
            $root->appendChild($doc->importNode($node, true));
        }
        return preg_replace('~^<nodes>|</nodes>$~si', '', $doc->saveHTML());
    }

    public function item($offset) {
        return $this->nodeList->item($offset);
    }

    /**
     * @return mixed
     */
    public function current() {
        return $this->item($this->offset);
    }

    /**
     * Move forward to next element
     */
    public function next()/*: void */ {
        $this->offset++;
    }

    /**
     * Return the key of the current element
     * @return mixed scalar on success, or null on failure.
     */
    public function key() {
        return $this->offset;
    }


    public function rewind()/*: void */ {
        $this->offset = 0;
    }

    public function valid(): bool {
        return (bool)$this->item($this->offset + 1);
    }
}
