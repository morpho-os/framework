<?php
namespace Morpho\Xml;

use Morpho\Fs\File;
use RuntimeException;
use InvalidArgumentException;

class XmlTool {
    /**
     * @return \Morpho\Xml\Document
     */
    public static function loadDomDocFromFile($filePath, array $options = array()) {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new InvalidArgumentException("Unable to load DOM document from the file '$filePath'.");
        }
        $source = File::read($filePath, ['binary' => false]);

        return self::loadDomDoc($source, $options);
    }

    /**
     * @return \Morpho\Xml\Document
     */
    public static function loadDomDoc($source, array $options = array()) {
        $source = trim($source);

        $doc = self::createDomDoc($options);

        $options += array(
            'fixHtmlEncoding' => false,
            'encoding' => 'utf-8',
        );

        libxml_use_internal_errors(true);

        if (substr($source, 0, 5) == '<?xml') {
            $result = $doc->loadXML($source);
        } else {
            if ($options['fixHtmlEncoding']) {
                $source = '<meta http-equiv="content-type" content="text/html; charset='
                    . escapeHtml($options['encoding']) . '">'
                    . $source;
            }
            $result = $doc->loadHTML($source);
        }

        libxml_use_internal_errors(false);

        if (!$result) {
            throw new RuntimeException('Unable to load document.');
        }

        return $doc;
    }

    /**
     * @return \Morpho\Xml\Document
     */
    public static function createDomDoc(array $options = array()) {
        $doc = new Document('1.0');
        $options += array(
            'preserveWhiteSpace' => false,
            'formatOutput' => true,
            'substituteEntities' => true,
            'encoding' => 'utf-8',
        );
        foreach ($options as $name => $value) {
            $doc->$name = $value;
        }

        return $doc;
    }

    /**
     * @return \Morpho\Xml\Document
     */
    public static function toXmlDoc(array $data, array $options = array()) {
        $doc = self::createDomDoc($options);
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
