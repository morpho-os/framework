<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Xml;

class Xml {
    public static function arrayToDomDoc(array $data, array $conf = []): Doc {
        $doc = Doc::mk($conf);
        self::arrayToXml($data, $doc);
        return $doc;
    }

    public static function nodeToXml(\DOMNode $node): string {
        return $node->ownerDocument->saveXML($node);
    }

    private static function arrayToXml(array $data, Doc $doc, $currentNode = null) {
        $currentNode = $currentNode ?: $doc;
        foreach ($data as $key => $value) {
            if (\is_array($value) || \is_object($value)) {
                if (\is_numeric($key)) {
                    self::arrayToXml($value, $doc, $currentNode);
                } else {
                    $node = $currentNode->appendChild($doc->createElement($key));
                    self::arrayToXml($value, $doc, $node);
                }
            } else {
                $currentNode->appendChild($doc->createElement($key, (string)$value));
            }
        }
    }




    /*
    private function innerHTML($element)
    {
        $innerHTML = "";
        $children = $element->childNodes;
        foreach ($children as $child) {
            $tmpDom = Xml::mkDomDoc();
            $tmpDom->appendChild($tmp_dom->importNode($child, true));
            $innerHTML.=trim($tmp_dom->saveHTML());
        }

        return $innerHTML;
    }
    */
}
