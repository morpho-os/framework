<?php
namespace Morpho\Xml;

use DOMXPath;
use DOMNodeList;
use RuntimeException;

class XPathQuery {
    private $xPath;

    public function __construct(\DOMDocument $doc) {
        $this->xPath = new DOMXPath($doc);
    }

    /**
     * @TODO: More effective algorithm.
     * @return null|DOMNode The first DOMNode that matches the XPath query or null if no matching node is found.
     */
    public function single($xpath, $contextNode = null) {
        return $this->all($xpath, $contextNode)->item(0);
    }

    /**
     * @return DOMNodeList A list of nodes matching the XPath query.
     */
    public function all($xpath, $contextNode = null) {
        $nodeList = $this->xpath($xpath, $contextNode);
        if (!$nodeList instanceof DOMNodeList) {
            throw new RuntimeException('Unable to select DOMNodeList, consider to use the xpath() method instead.');
        }

        return $nodeList;
    }

    public function xpath($xpath, $contextNode = null) {
        if (null !== $contextNode) {
            $result = $this->xPath->evaluate($xpath, $contextNode);
        } else {
            $result = $this->xPath->evaluate($xpath);
        }
        if (false === $result) {
            throw new RuntimeException("The XPath expression '$xpath' is not valid.");
        }

        return $result;
    }

    public function getXpath($node) {
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

    public function position($xpath) {
        $xpath = "count($xpath/preceding-sibling::*)+1";

        return $this->xpath($xpath);
    }
}
