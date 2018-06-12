<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Xml;

use DOMDocument;

use Morpho\Base\InvalidConfigException;
use Morpho\Fs\File;
use Morpho\App\Web\View\Html;

/**
 * @method XPathResult select(string $xPath, $contextNode = null)
 */
class Doc extends DOMDocument {
    private $xPath;

    const ENCODING = 'utf-8';

    /**
     * NB: true values are not actual values of the options.
     */
    private const CREATE_CONFIG_PARAMS = [
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

    public static function parseFile(string $filePath, array $config = null): Doc {
        if (!\is_file($filePath) || !\is_readable($filePath)) {
            throw new \InvalidArgumentException("Unable to load DOM document from the file '$filePath'");
        }
        $source = File::read($filePath, ['binary' => false]);
        return self::parse($source, $config);
    }

    public static function parse(string $source, array $config = null): Doc {
        $source = \trim($source);

        $config = (array) $config;
        $fixEncoding = $config['fixEncoding'] ?? false;
        unset($config['fixEncoding']);

        $doc = self::mk($config);

        \libxml_use_internal_errors(true);

        if (\substr($source, 0, 5) == '<?xml') {
            $result = $doc->loadXML($source);
        } else {
            if ($fixEncoding) {
                $source = '<meta http-equiv="content-type" content="text/html; charset=' . Html::encode($config['encoding'] ?? self::ENCODING) . '">'
                    . $source;
            }
            $result = $doc->loadHTML($source);
        }

        \libxml_use_internal_errors(false);

        if (!$result) {
            throw new \RuntimeException('Unable to load document.');
        }

        return $doc;
    }

    public static function mk(array $config = null): Doc {
        $config = (array) $config;
        $invalidConfig = \array_diff_key($config, self::CREATE_CONFIG_PARAMS);
        if (\count($invalidConfig)) {
            throw new InvalidConfigException($invalidConfig);
        }

        $doc = new Doc('1.0');
        $config += [
            'preserveWhiteSpace' => false,
            'formatOutput'       => true,
            'substituteEntities' => true,
            'encoding'           => self::ENCODING,
        ];
        foreach ($config as $name => $value) {
            $doc->$name = $value;
        }

        return $doc;
    }

    public function __call($method, $args) {
        return \call_user_func_array([$this->xPath(), $method], $args);
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
