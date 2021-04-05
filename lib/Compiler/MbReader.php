<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler;

use Morpho\Base\Env;
use Morpho\Base\NotImplementedException;

/**
 * MB/Multi-Byte string reader. Userful in writing recursive descent parsers.
 * Based on [stringscanner from ruby](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html), see [license](https://github.com/ruby/ruby/blob/master/COPYING)
 * Supposed to work only with UTF-8 strings.
 */
class MbReader implements IStringReader {
    /*
    protected $lineNo = 1;
    //protected string $input;
    protected string $input;
    protected int $offset = 0;
    protected int $charOffset = 0;
    protected ?string $encoding;
    *

    public function __construct(string $s, string $encoding = null) {
        $this->input = $s;
        // Use the mb_list_encodings() to get list of available encodings, of the all possible values see http://php.net/manual/en/mbstring.supported-encodings.php
        $this->encoding = $encoding;
    }
*/
    /**
     * MbReader constructor.
     * @param string $input
     * @param bool $anchored
     */



    public function charpos();
    public function getch();


    // Advancing the Scan Pointer

    // Original: getch
    public function char(): ?string {
        throw new NotImplementedException();
        /*
        if ($this->eos()) {
            return null;
        }
        $ch = mb_substr($this->input, $this->charOffset, 1, $this->encoding());
        $this->charOffset++;
        $this->offset += \strlen($ch);
        return $ch;
        */
    }

    // Original: get_byte
    public function byte(): ?string {
        if ($this->eos()) {
            return null;
        }
        $ch = \substr($this->input, $this->offset, 1);
        if (false === $ch) {
            throw new \RuntimeException();
        }
        $this->offset++;

        // @TODO: Unicode
        $this->charOffset++;

        /*
        $s = \substr($this->input, 0, $this->offset);
        $this->charOffset = mb_strlen($s, $this->encoding());
        */

        return $ch;
    }

    public function read(string $re): ?string {
        throw new NotImplementedException();
        /**
         * To match from the current position use the `A/PCRE_ANCHORED` modifier, e.g.:
         *     $s = ' ab';
         *     preg_match('~ab~', $s);  // evaluates to 1
         *     preg_match('~ab~A', $s); // evaluates to 0
         */
        /*
        if (\preg_match($re, $this->input, $m, 0, $this->charOffset)) {
            $s = \array_shift($m);
            //$this->offset += \strlen($s);
            $this->charOffset += mb_strlen($s);
            return $s;
        }
        return null;
        */
    }

    /**
     * Scans the string until the pattern is matched. Returns the substring up to and including the end of the match, advancing the scan pointer to that location. If there is no match, null is returned.
     */
    public function scanUntil(string $re): ?string {
        throw new NotImplementedException();
        /*
        if (\preg_match($re, $this->input, $m, PREG_OFFSET_CAPTURE, $this->charOffset)) {
            list($s, $offset) = $m[0];
        }
        */
    }
    /*
    $encoding = $this->encoding();
    mb_substr($this->input, $this->offset, )
    */
    /*
    $n = $offset - $this->offset + mb_strlen($match, $encoding);
    $s = mb_substr($this->input, $this->offset, $n, $encoding); * /
    $this->offset += \strlen($s);
    return $s;
}
return null;
}
    */

    /**
     * Tests whether the given pattern is matched from the current scan pointer. Advances the scan pointer if advance_pointer_p is true. Returns the matched string if return_string_p is true. The match register is affected. “full” means “#scan with full parameters”.
     */
    public function scanFull(string $re, bool $advancePointer, bool $returnString) {
        throw new NotImplementedException();
    }

    public function skip() {
        throw new NotImplementedException();
    }

    public function skipUntil() {
        throw new NotImplementedException();
    }

    public function searchFull() {
        throw new NotImplementedException();
    }

    // Looking Ahead

    public function check() {
        throw new NotImplementedException();
    }

    public function checkUntil() {
        throw new NotImplementedException();
    }

    // Original: exist
    public function exists(): bool {
        throw new NotImplementedException();
    }

    /**
     * Original: match?(pattern)
     * Tests whether the given pattern is matched from the current scan pointer. Returns the length of the match, or nil. The scan pointer is not advanced.
     */
    public function matchLength(): ?int {
        throw new NotImplementedException();
    }

    public function peek() {
        throw new NotImplementedException();
    }

    // Finding Where we Are

    public function beginningOfLine(): bool {
        throw new NotImplementedException();
    }

    public function eos(): bool {
        return $this->offset >= \strlen($this->input);
    }

    public function rest() {
        throw new NotImplementedException();
    }

    public function restSize() {
        throw new NotImplementedException();
    }

    // Original: charpos
    public function charOffset(): int {
        return $this->charOffset;
    }

    // Original: pos=
    public function setOffset(int $offset): self {
        throw new NotImplementedException();
    }

    // Original: pos
    public function offset(): int {
        return $this->offset;
    }
    // Setting Where we Are

    public function reset() {
        $this->charOffset = $this->offset = 0;
    }

    public function terminate() {
        throw new NotImplementedException();
    }

    // Match Data

    /**
     * Original: matched()
     * Returns the last matched string.
     */
    public function lastMatched(): string {
        throw new NotImplementedException();
    }

    public function matched(): bool {
        throw new NotImplementedException();
    }

    // []
    public function matchedSubgroup(int $i) {
        throw new NotImplementedException();
    }

    /**
     * Returns the size of the most recent match (see matched), or nil if there was no recent match.
     */
    public function matchedSize() {
        throw new NotImplementedException();
    }

    /**
     * Return the pre-match (in the regular expression sense) of the last scan.
     */
    public function preMatch(): ?string {
        return null;
    }

    public function postMatch() {
        throw new NotImplementedException();
    }

    // Miscellaneous

    // Original: <<
    public function append() {
        throw new NotImplementedException();
    }

    public function concat() {
        throw new NotImplementedException();
    }

    // Original: string
    public function input(): string {
        return $this->input;
    }

    // Original: string=
    public function setInput(string $input): self {
        $this->input = $input;
        return $this;
    }

    public function unread() {
        throw new NotImplementedException();
    }

    public function inspect() {
        throw new NotImplementedException();
    }

    private function encoding(): string {
        return $this->encoding ?: Env::ENCODING;
    }
    /*
    public function __invoke($value) {
        //$this->offset = 0;
        //$this->input = $uri;
        // TODO: Implement __invoke() method.
        //return $ast;
    }

    protected function lookAhead(int $n): string {
        return mb_substr($this->input, $this->offset, $n);
    }

    protected function find(string $needle): int|false {
        return mb_strpos($this->input, $needle, $this->offset);
    }

    protected function consume(string $string): void {
        $n = mb_strlen($string);
        $next = mb_substr($this->input, $this->offset, $n);
        if ($next !== $string) {
            throw new ParseException('Invalid URI');
        }
        $this->offset += $n;
    }
    */

/*
    public function __construct(string $input) {
        $this->input = $input;
    }

    /*    public function input(): string {
            return $this->input;
        }* /

    public function lineNo(): int {
        return $this->lineNo;
    }

    public function offset(): int {
        return $this->offset;
    }

    /*    public function setOffset(int $offset) {
            $this->offset = $offset;
        }* /

    /**
     * @return bool|string
     * /
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
         * /
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
*/
}
