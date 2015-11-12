<?php
namespace Morpho\Xml;

class XPathQuery {
    private $xPath;

    public function __construct(\DOMDocument $doc) {
        $this->xPath = new \DOMXPath($doc);
    }

    /**
     * @TODO: More effective algorithm.
     * @return null|DOMNode The first DOMNode that matches the XPath query or null if no matching node is found.
     */
    public function single(string $xPath, $contextNode = null) {
        return $this->all($xPath, $contextNode)->head();
    }

    public function all(string $xPath, $contextNode = null): XPathResult {
        $nodeList = $this->xPath($xPath, $contextNode);
        if (!$nodeList instanceof \DOMNodeList) {
            throw new \RuntimeException('Unable to select DOMNodeList, consider to use the xPath() method instead.');
        }

        return new XPathResult($nodeList);
    }

    public function xPath(string $xPath, $contextNode = null) {
        if (null !== $contextNode) {
            $result = $this->xPath->evaluate($xPath, $contextNode);
        } else {
            $result = $this->xPath->evaluate($xPath);
        }
        if (false === $result) {
            throw new \RuntimeException("The XPath expression '$xPath' is not valid.");
        }

        return $result;
    }

    public function getXPath($node) {
        /*
        @TODO
        if ($node instanceof SimpleXMLElement) {
                $node = dom_import_simplexml($node);
        } elseif (!$node instanceof DOMNode) {
                die('Not a node?');
        }

        $q         = new DOMXPath($node->ownerDocument);
        $xpath = '';

        do {
                $position = 1 + $q->query('preceding-sibling::*[name()="' . $node->nodeName . '"]', $node)->length;
                $xpath        = '/' . $node->nodeName . '[' . $position . ']' . $xpath;
                $node         = $node->parentNode;
        } while (!$node instanceof DOMDocument);

        return $xpath;
        */
    }

    public function position($xPath) {
        $xpath = "count($xPath/preceding-sibling::*)+1";

        return $this->xpath($xPath);
    }
}