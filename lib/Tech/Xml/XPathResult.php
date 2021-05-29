<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Xml;

use Countable;

class XPathResult implements \Iterator, Countable {
    /**
     * @var \DOMNodeList
     */
    protected $nodeList;

    /**
     * @var int
     */
    protected $offset = 0;

    public function __construct(\DOMNodeList $nodeList) {
        $this->nodeList = $nodeList;
    }

    public function toHtml(array $conf = null): string {
        $doc = Doc::mk($conf);
        foreach ($this->nodeList as $node) {
            $doc->appendChild($doc->importNode($node, true));
        }
        return $doc->saveHTML();
    }

    public function head(): ?\DOMElement {
        return $this->nodeList->item(0);
    }

    public function tail(): array {
        $first = false;
        $res = [];
        foreach ($this->nodeList as $node) {
            if ($first) {
                $res[] = $node;
            } else {
                $first = true;
            }
        }
        return $res;
    }

    public function last(): ?\DOMElement {
        return $this->item($this->count() - 1);
    }

    public function item(int $offset): ?\DOMElement {
        return $this->nodeList->item($offset);
    }

    public function count(): int {
        return $this->nodeList->length;
    }

    public function init(): array {
        $stop = $this->count() - 1;
        $res = [];
        foreach ($this->nodeList as $i => $node) {
            if ($i === $stop) {
                return $res;
            }
            $res[] = $node;
        }
        return $res;
    }

    public function current() {
        return $this->item($this->offset);
    }

    public function next(): void {
        $this->offset++;
    }

    public function key() {
        return $this->offset;
    }

    public function rewind(): void {
        $this->offset = 0;
    }

    public function valid(): bool {
        return (bool) $this->item($this->offset);
    }
}
