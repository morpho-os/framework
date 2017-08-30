<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types=1);
namespace Morpho\Code;

use const Morpho\Base\EOL_FULL_RE;
use Morpho\Base\Must;
use Morpho\Base\NotImplementedException;

/**
 * Signatures of some methods taken from http://ruby-doc.org/stdlib-2.4.1/libdoc/strscan/rdoc/StringScanner.html, in particular:
 * - scan(pattern) => String
 * - eos?()
 */
class StringReader {
    protected $input;
    protected $offset = 0;
    protected $lineNo = 1;

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
        if (null !== $ch) {
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
        $this->lineNo += preg_match_all(EOL_FULL_RE, $s);
        return $s;
    }

    public function peek1(): ?string {
        $ch = substr($this->input, $this->offset, 1);
        if ($ch === '' || $ch === false) {
            return null;
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

    public function read(string $expected): string {
        $s = $this->readN(strlen($expected));
        if ($s !== $expected) {
            throw new SyntaxError("Read input doesn't match expected input: expected: '$expected', got: '$s'");
        }
        return $s;
    }

    public function matches(string $re): bool {
        return (bool) preg_match($re, $this->input, $m, 0, $this->offset);
    }

    public function scan(string $re): ?string {
        /**
         * To match from the current position use the `A/PCRE_ANCHORED` modifier, e.g.:
         *     $s = ' ab';
         *     preg_match('~ab~', $s);  // evaluates to 1
         *     preg_match('~ab~A', $s); // evaluates to 0
         */
        if (preg_match($re, $this->input, $m, 0, $this->offset)) {
            $s = array_shift($m);
            $this->offset += strlen($s);
            return $s;
        }
        return null;
    }

    public function readMatching(string $re): string {
        if (!preg_match($re, $this->input, $m, 0, $this->offset)) {
            throw new SyntaxError();
        }
        $s = array_shift($m);
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

    public function readSingleQuotedString(): string {
        // The regular expression taken from nikic/php-parser package, grammar/rebuildParsers.php file.
        if (!preg_match('~(?<singleQuotedString>\'[^\\\\\']*+(?:\\\\.[^\\\\\']*+)*+\')~si', $this->input, $match, 0, $this->offset)) {
            throw new SyntaxError();
        }
        $str = $match['singleQuotedString'];
        $this->offset += strlen($str);
        return $str;
    }

    public function readUntil(callable $predicate): string {
        throw new NotImplementedException();
    }

    public function eos(): bool {
        return $this->offset >= strlen($this->input);
    }
}