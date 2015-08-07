<?php
namespace Morpho\Xml;

use DOMDocument;

class Document extends DOMDocument {
    private $xPath;

    public function __call($method, $args) {
        return call_user_func_array(array($this->getXpath(), $method), $args);
    }

    private function getXpath() {
        if (null === $this->xPath) {
            $this->xPath = new XPathQuery($this);
        }

        return $this->xPath;
    }
    /*
    public function addDomNode(DOMDocument $doc, $parentNode, $name, $value, array $attributes = array())
    {
      $node = $parentNode->appendChild($doc->createElement($name, htmlspecialchars($value, ENT_QUOTES)));
      foreach ($attributes as $name => $value) {
        $node->setAttribute($name, $value);
      }
    //  $element->appendChild($doc->createTextNode($value));
      return $node;
    }
    */
}
