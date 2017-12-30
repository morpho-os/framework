<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

use Morpho\Code\Parsing\SyntaxError;

class DeclareStmtManager {
    public const ON_FIRST_LINE = 0;
    public const ON_SECOND_LINE = 1;
    public const AFTER_FIRST_MULTI_COMMENT = 2;

    private const TOKENS = [
        'openTag' => '~^<\?php(?:[\040\t]|\n)~siA',
        'multiComment' => '~/\*.*?\*/~siA',
        'singleComment' => '~//[^\n\r]*~siA',
        'declareStmt' => '~declare\s*\(strict_types\s*=\s*[01]\s*\)\s*;~siA',
        //'otherCode' => '~(?<otherCode>\S+)~siA',
    ];

    private const OPEN_TAG_NODE = 'openTag';
    private const SINGLE_COMMENT_NODE = 'singleComment';
    private const MULTI_COMMENT_NODE = 'multiComment';
    private const DECLARE_STMT_NODE = 'declareStmt';
    private const OTHER_CODE_NODE = 'otherCode';

    /**
     * @var int
     */
    private $offset;
    /**
     * @var string
     */
    private $code;
    /**
     * @var array
     */
    private $ast;

    public function removeCommentedOutDeclareStmt(string $code): string {
        $this->parse($code);
        if (!$this->ast) {
            return $code;
        }
        $locations = [];
        foreach ($this->ast as $node) {
            switch ($node['type']) {
                case self::OPEN_TAG_NODE:
                case self::DECLARE_STMT_NODE:
                case self::MULTI_COMMENT_NODE:
                case self::OTHER_CODE_NODE:
                    break;
                case self::SINGLE_COMMENT_NODE:
                    if (preg_match('~//\s*declare\s*\(strict[^\n\r]*~siA', $node['value'], $match)) {
                        $locations[] = [$node['offset'], strlen($match[0])];
                    }
                    break;
                default:
                    throw new \UnexpectedValueException();
            }
        }
        if ($locations) {
            return $this->removeLocations($code, $locations);
        }
        return $code;
    }

    public function removeDeclareStmt(string $code): string {
        $this->parse($code);
        if (!$this->ast) {
            return $code;
        }
        $locations = [];
        foreach ($this->ast as $node) {
            switch ($node['type']) {
                case self::DECLARE_STMT_NODE:
                    $locations[] = [$node['offset'], strlen($node['value'])];
                    break;
                case self::OPEN_TAG_NODE:
                case self::MULTI_COMMENT_NODE:
                case self::OTHER_CODE_NODE:
                case self::SINGLE_COMMENT_NODE:
                    break;
                default:
                    throw new \UnexpectedValueException();
            }
        }
        if ($locations) {
            return $this->removeLocations($code, $locations);
        }
        return $code;
    }

    public function addDeclareStmt(string $code, int $position): string {
        $this->parse($code);
        $locations = [];
        foreach ($this->ast as $node) {
            switch ($node['type']) {
                case self::DECLARE_STMT_NODE:
                    $locations[] = [$node['offset'], strlen($node['value'])];
                    break;
                case self::OPEN_TAG_NODE:
                case self::MULTI_COMMENT_NODE:
                case self::OTHER_CODE_NODE:
                case self::SINGLE_COMMENT_NODE:
                    break;
                default:
                    throw new \UnexpectedValueException();
            }
        }
        if ($locations) {
            return $this->removeLocations($code, $locations);
        }
        return $code;
    }

    private function removeLocations(string $code, array $locs): string {
        $newCode = '';
        $p = 0;
        foreach ($locs as $loc) {
            [$offset, $length] = $loc;
            $newCode .= substr($code, $p, $offset);
            if (preg_match('~^<\?php\s+$~si', $newCode)) {
                $newCode = rtrim($newCode) .  substr($code, $offset + $length);
            } else {
                $newCode .= ltrim(substr($code, $offset + $length));
            }
            $p += $length;
        }
        return rtrim($newCode);
    }

    private function parse(string $code): void {
        $this->ast = new \ArrayObject();
        $this->code = $code;
        $this->offset = 0;
        $n = strlen($code);
        $node = $this->laNextNode(self::OPEN_TAG_NODE);
        if (!$node) {
            return;
        }
        $this->emit($node);

        $declareFound = false;
        while ($this->offset < $n) {
            // @TODO: Preserve spaces
            $this->skipOptionalSpaces();
            $node = $this->laNextNode(self::MULTI_COMMENT_NODE);
            if ($node) {
                $this->emit($node);
                //$this->skipOptionalSpaces();
                continue;
            }
            $node = $this->laNextNode(self::SINGLE_COMMENT_NODE);
            if ($node) {
                $this->emit($node);
                //$this->skipOptionalSpaces();
                continue;
            }
            if (!$declareFound) {
                $node = $this->laNextNode(self::DECLARE_STMT_NODE);
                if ($node) {
                    $this->emit($node);
                    //$this->skipOptionalSpaces();
                    $declareFound = true;
                    continue;
                }
            }
            if (preg_match('~\S~siA', $this->code, $match, 0, $this->offset)) {
                $value = substr($this->code, $this->offset);
                $this->emit($this->newNode($value, self::OTHER_CODE_NODE));
                break;
            }
        }
    }

    /**
     * @return \ArrayObject|false Returns a Node or false
     */
    private function laNextNode(string $nodeType) {
        $re = self::TOKENS[$nodeType];
        if (!preg_match($re, $this->code, $match, 0, $this->offset)) {
            return false;
        }
        $value = array_pop($match);
        return $this->newNode($value, $nodeType);
    }

    private function newNode(string $value, string $type): \ArrayObject {
        return new \ArrayObject([
            'value' => $value,
            'type' => $type,
            'offset' => $this->offset,
        ]);
    }

    private function emit(\ArrayObject $node): void {
        $this->offset += strlen($node['value']);
        $this->ast[] = $node;
    }

    private function skipOptionalSpaces(): void {
        $this->skip('~\s+~siA', false);
    }

    private function skip(string $re, bool $required): void {
        if (!preg_match($re, $this->code, $match, 0, $this->offset)) {
            if ($required) {
                throw new SyntaxError();
            }
            return;
        }
        $this->offset += strlen(array_pop($match));
    }
}