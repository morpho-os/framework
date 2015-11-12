<?php
namespace Morpho\Xml;

use Morpho\Base\NotImplementedException;

class XPathResult {
    private $nodeList;

    public function __construct(\DOMNodeList $nodeList) {
        $this->nodeList = $nodeList;
    }

    public function filter(callable $filter) {
        throw new NotImplementedException();
    }

    public function head() {
        return $this->nodeList->item(0);
    }

    public function tail() {
        throw new NotImplementedException();
    }

    public function last() {
        throw new NotImplementedException();
    }

    public function init() {
        throw new NotImplementedException();
    }

    public function toHtml() {
        $doc = Document::create();
        $root = $doc->appendChild($doc->createElement('nodes'));
        foreach ($this->nodeList as $node) {
            $root->appendChild($doc->importNode($node, true));
        }
        return preg_replace('~^<nodes>|</nodes>~si', '', $doc->saveHTML());
    }

    public function __call($method, $args) {
        $this->nodeList->$method(...$args);
    }
}
