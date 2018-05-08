<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Infra;

class DeclareStmtManager {
    public const AT_FIRST_LINE = 0;
    public const AT_SECOND_LINE = 1;
    public const AFTER_FIRST_MULTI_COMMENT = 2;

    private const TOKENS = [
        'openTag' => '~^<\?php(?:[\040\t]|\n)?~siA',
        'multiComment' => '~/\*.*?\*/~siA',
        'singleComment' => '~//[^\n\r]*~siA',
        'declareStmt' => '~declare\s*\(strict_types\s*=\s*[01]\s*\)\s*;~siA',
        'whitespace' => '~\s+~siA',
    ];

    private const OPEN_TAG_NODE = 'openTag';
    private const SINGLE_COMMENT_NODE = 'singleComment';
    private const MULTI_COMMENT_NODE = 'multiComment';
    private const DECLARE_STMT_NODE = 'declareStmt';
    private const OTHER_CODE_NODE = 'otherCode';
    private const WHITESPACE_NODE = 'whitespace';

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
        $code = '';
        foreach ($this->ast as $node) {
            switch ($node['type']) {
                case self::OPEN_TAG_NODE:
                case self::DECLARE_STMT_NODE:
                case self::MULTI_COMMENT_NODE:
                case self::OTHER_CODE_NODE:
                case self::WHITESPACE_NODE:
                    $code .= $node['value'];
                    break;
                case self::SINGLE_COMMENT_NODE:
                    if (\preg_match('~//\s*declare\s*\(strict[^\n\r]*~siA', $node['value'])) {
                        if (\preg_match('~^<\?php\s+~', $code)) {
                            $code = \rtrim($code);
                        }
                        break;
                    }
                    $code .= $node['value'];
                    break;
                default:
                    throw new \UnexpectedValueException();
            }
        }
        return $code;
    }

    public function removeDeclareStmt(string $code): string {
        $this->parse($code);
        $code = '';
        foreach ($this->ast as $node) {
            switch ($node['type']) {
                case self::DECLARE_STMT_NODE:
                    if (\preg_match('~^<\?php\s+~', $code)) {
                        $code = \rtrim($code);
                    }
                    break;
                case self::OPEN_TAG_NODE:
                case self::MULTI_COMMENT_NODE:
                case self::OTHER_CODE_NODE:
                case self::SINGLE_COMMENT_NODE:
                case self::WHITESPACE_NODE:
                    $code .= $node['value'];
                    break;
                default:
                    throw new \UnexpectedValueException();
            }
        }
        return $code;
    }

    public function addDeclareStmt(string $code, int $position): string {
        $this->parse($code);

        if (!\count($this->ast)) {
            return '';
        }
        foreach ($this->ast as $node) {
            switch ($node['type']) {
                case self::DECLARE_STMT_NODE:
                    return $code;
            }
        }

        $code = '';
        $declareStmt = 'declare(strict_types=1);';
        $added = false;
        foreach ($this->ast as $index => $node) {
            switch ($node['type']) {
                case self::OPEN_TAG_NODE:
                    if ($position === self::AT_FIRST_LINE) {
                        $code .= \rtrim($node['value']) . " $declareStmt" . (isset($this->ast[$index + 1]) ? "\n" : '');
                    } elseif ($position === self::AT_SECOND_LINE) {
                        $code .= \rtrim($node['value']) . "\n$declareStmt" . (isset($this->ast[$index + 1]) ? "\n" : '');
                    } else {
                        $code .= $node['value'];
                    }
                    break;
                case self::MULTI_COMMENT_NODE:
                    if (!$added && $position === self::AFTER_FIRST_MULTI_COMMENT) {
                         $code .= \rtrim($node['value']) . "\n$declareStmt";
                         $added = true;
                    } else {
                        $code .= $node['value'];
                    }
                    break;
                case self::SINGLE_COMMENT_NODE:
                case self::WHITESPACE_NODE:
                case self::OTHER_CODE_NODE:
                    $code .= $node['value'];
                    break;
                default:
                    throw new \UnexpectedValueException();
            }
        }
        return $code;
    }

    private function parse(string $code): void {
        $this->ast = new \ArrayObject();
        $this->code = $code;
        $this->offset = 0;
        $n = \strlen($code);

        $node = $this->laNextNode(self::OPEN_TAG_NODE);
        if (!$node) {
            return;
        }
        $this->emit($node);

        $declareFound = false;
        while ($this->offset < $n) {
            $node = $this->laNextNode(self::WHITESPACE_NODE);
            if ($node) {
                $this->emit($node);
                continue;
            }
            $node = $this->laNextNode(self::MULTI_COMMENT_NODE);
            if ($node) {
                $this->emit($node);
                continue;
            }
            $node = $this->laNextNode(self::SINGLE_COMMENT_NODE);
            if ($node) {
                $this->emit($node);
                continue;
            }
            if (!$declareFound) {
                $node = $this->laNextNode(self::DECLARE_STMT_NODE);
                if ($node) {
                    $this->emit($node);
                    $declareFound = true;
                    continue;
                }
            }
            if (\preg_match('~\S~siA', $this->code, $match, 0, $this->offset)) {
                $value = \substr($this->code, $this->offset);
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
        if (!\preg_match($re, $this->code, $match, 0, $this->offset)) {
            return false;
        }
        $value = \array_pop($match);
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
        $this->offset += \strlen($node['value']);
        $this->ast[] = $node;
    }
}
