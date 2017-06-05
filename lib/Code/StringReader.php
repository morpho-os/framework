<?php
declare(strict_types=1);

namespace Morpho\Code;

use const Morpho\Base\EOL_RE;
use Morpho\Base\Must;

class StringReader {
    private $input;
    private $offset = 0;
    private $lineNo = 1;

    public function __construct(string $input) {
        $this->input = $input;
    }

/*    public function input(): string {
        return $this->input;
    }*/

    public function lineNo(): int {
        return $this->lineNo;
    }

    public function offset(): int {
        return $this->offset;
    }

/*    public function setOffset(int $offset) {
        $this->offset = $offset;
    }*/

    /**
     * @return bool|string
     */
    public function read1() {
        $ch = $this->peek1();
        if (false !== $ch) {
            $this->offset++;
            if ($ch === "\n") {
                $this->lineNo++;
            }
        }
        return $ch;
    }

    public function readN(int $n): string {
        $s = $this->peekN($n);
        $this->offset += $n;
        $this->lineNo += preg_match_all(EOL_RE, $s);
        return $s;
    }

    /**
     * @return bool|string
     */
    public function peek1() {
        $ch = substr($this->input, $this->offset, 1);
        if ($ch === '' || $ch === false) {
            return false;
        }
        return $ch;
    }

    public function peekN(int $n): string {
        Must::beTrue($n >= 0);
        if ($n === 0) {
            return '';
        }
        if (($this->offset + $n) > strlen($this->input)) {
            throw new \OutOfBoundsException();
        }
        $s = substr($this->input, $this->offset, $n);
        if ($s === '' || false === $s) {
            throw new \OutOfBoundsException();
        }
        return $s;
    }

    public function unread1(): string {
        if ($this->offset === 0) {
            throw new \OutOfBoundsException();
        }
        $ch = substr($this->input, $this->offset - 1, 1);
        if ($ch === "\n") {
            $this->lineNo--;
        }
        $this->offset--;
        return $ch;
    }

    public function skip(string $expected): void {
        $s = $this->readN(strlen($expected));
        if ($s !== $expected) {
            throw new SyntaxError("Read input doesn't match expected input: expected: '$expected', got: '$s'");
        }
    }

    public function readRe(string $re): string {
        if (!preg_match($re, $this->input, $m, 0, $this->offset)) {
            throw new SyntaxError();
        }
        $s = array_pop($m);
        $this->offset += strlen($s);
        return $s;
    }

    public function readDoubleQuotedString(): string {
        // The regular expression taken from nikic/php-parser package, grammar/rebuildParsers.php file.
        if (!preg_match('~(?<doubleQuotedString>"[^\\\\"]*+(?:\\\\.[^\\\\"]*+)*+")~si', $this->input, $match, 0, $this->offset)) {
            throw new SyntaxError();
        }
        $str = $match['doubleQuotedString'];
        $this->offset += strlen($str);
        return $str;
    }
}