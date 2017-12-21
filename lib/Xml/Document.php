<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Xml;

use DOMDocument;

use Morpho\Base\InvalidOptionsException;
use Morpho\Fs\File;
use Morpho\Web\View\Html;

/**
 * @method XPathResult select(string $xPath, $contextNode = null)
 */
class Document extends DOMDocument {
    private $xPath;

    const ENCODING = 'utf-8';

    /**
     * NB: true values are not actual values of the options.
     */
    private const CREATE_VALID_OPTIONS = [
        'documentURI' => true,
        'encoding' => true,
        'formatOutput' => true,
        'preserveWhiteSpace' => true,
        'recover' => true,
        'resolveExternals' => true,
        'strictErrorChecking' => true,
        'substituteEntities' => true,
        'validateOnParse' => true,
        'xmlStandalone' => true,
        'xmlVersion' => true,
    ];

    public static function parseFile(string $filePath, array $options = null): Document {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException("Unable to load DOM document from the file '$filePath'");
        }
        $source = File::read($filePath, ['binary' => false]);
        return self::parse($source, $options);
    }

    public static function parse(string $source, array $options = null): Document {
        $source = trim($source);

        $options = (array) $options;
        $fixEncoding = $options['fixEncoding'] ?? false;
        unset($options['fixEncoding']);

        $doc = self::new($options);

        libxml_use_internal_errors(true);

        if (substr($source, 0, 5) == '<?xml') {
            $result = $doc->loadXML($source);
        } else {
            if ($fixEncoding) {
                $source = '<meta http-equiv="content-type" content="text/html; charset=' . Html::encode($options['encoding'] ?? self::ENCODING) . '">'
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

    public static function new(array $options = null): Document {
        $options = (array) $options;
        $invalidOptions = array_diff_key($options, self::CREATE_VALID_OPTIONS);
        if (count($invalidOptions)) {
            throw new InvalidOptionsException($invalidOptions);
        }

        $doc = new Document('1.0');
        $options += [
            'preserveWhiteSpace' => false,
            'formatOutput'       => true,
            'substituteEntities' => true,
            'encoding'           => self::ENCODING,
        ];
        foreach ($options as $name => $value) {
            $doc->$name = $value;
        }

        return $doc;
    }

    public function __call($method, $args) {
        return call_user_func_array([$this->xPath(), $method], $args);
    }

    public function xPath(): XPathQuery {
        if (null === $this->xPath) {
            $this->xPath = new XPathQuery($this);
        }
        return $this->xPath;
    }

    public function namespaces() {
        $xpath = new \DOMXPath($this);
        foreach ($xpath->query("namespace::*", $this->documentElement) as $node) {
            yield $node->localName => $node->nodeValue;
        }
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
