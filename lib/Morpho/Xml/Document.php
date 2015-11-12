<?php
namespace Morpho\Xml;

use DOMDocument;

use function Morpho\Base\escapeHtml;
use Morpho\Fs\File;

class Document extends DOMDocument {
    private $xPath;

    public static function fromFile(string $filePath, array $options = []): Document {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException("Unable to load DOM document from the file '$filePath'.");
        }
        $source = File::read($filePath, ['binary' => false]);
        return self::fromString($source, $options);
    }

    public static function fromString(string $source, array $options = []): Document {
        $source = trim($source);

        $doc = self::create($options);

        $options += [
            'fixHtmlEncoding' => false,
            'encoding'        => 'utf-8',
        ];

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
            throw new \RuntimeException('Unable to load document.');
        }

        return $doc;
    }

    public static function create(array $options = []): Document {
        $doc = new Document('1.0');
        $options += [
            'preserveWhiteSpace' => false,
            'formatOutput'       => true,
            'substituteEntities' => true,
            'encoding'           => 'utf-8',
        ];
        foreach ($options as $name => $value) {
            $doc->$name = $value;
        }

        return $doc;
    }

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
