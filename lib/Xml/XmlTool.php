<?php
namespace Morpho\Xml;

class XmlTool {
    public static function arrayToDomDoc(array $data, array $options = []): Document {
        $doc = Document::create($options);
        self::arrayToXml($data, $doc);
        return $doc;
    }

    private static function arrayToXml(array $data, Document $doc, $currentNode = null) {
        $currentNode = $currentNode ?: $doc;
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                if (is_numeric($key)) {
                    self::arrayToXml($value, $doc, $currentNode);
                } else {
                    $node = $currentNode->appendChild($doc->createElement($key));
                    self::arrayToXml($value, $doc, $node);
                }
            } else {
                $currentNode->appendChild($doc->createElement($key, $value));
            }
        }
    }

    /*
    private function innerHTML($element)
    {
        $innerHTML = "";
        $children = $element->childNodes;
        foreach ($children as $child) {
            $tmp_dom = XmlTool::createDomDoc();
            $tmp_dom->appendChild($tmp_dom->importNode($child, true));
            $innerHTML.=trim($tmp_dom->saveHTML());
        }

        return $innerHTML;
    }
    */
}
