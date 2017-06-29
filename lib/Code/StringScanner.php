<?php
declare(strict_types=1);

namespace Morpho\Code;
use Morpho\Base\Environment;
use Morpho\Base\NotImplementedException;

/**
 * Based on http://ruby-doc.org/stdlib-2.4.1/libdoc/strscan/rdoc/StringScanner.html
 */
class StringScanner {
    /**
     * @var string
     */
    protected $input;
    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var int
     */
    protected $charOffset = 0;

    /**
     * @var ?string
     */
    protected $encoding;

    public function __construct(string $s, string $encoding = null) {
        $this->input = $s;
        // Use the mb_list_encodings() to get list of available encodings, of the all possible values see http://php.net/manual/en/mbstring.supported-encodings.php
        $this->encoding = $encoding;
    }

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
        $this->offset += strlen($ch);
        return $ch;
        */
    }

    // Original: get_byte
    public function byte(): ?string {
        if ($this->eos()) {
            return null;
        }
        $ch = substr($this->input, $this->offset, 1);
        if (false === $ch) {
            throw new \RuntimeException();
        }
        $this->offset++;

        // @TODO: Unicode
        $this->charOffset++;

        /*
        $s = substr($this->input, 0, $this->offset);
        $this->charOffset = mb_strlen($s, $this->encoding());
        */

        return $ch;
    }

    public function scan(string $re): ?string {
        throw new NotImplementedException();
        /**
         * To match from the current position use the `A/PCRE_ANCHORED` modifier, e.g.:
         *     $s = ' ab';
         *     preg_match('~ab~', $s);  // evaluates to 1
         *     preg_match('~ab~A', $s); // evaluates to 0
         */
        /*
        if (preg_match($re, $this->input, $m, 0, $this->charOffset)) {
            $s = array_shift($m);
            //$this->offset += strlen($s);
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
        if (preg_match($re, $this->input, $m, PREG_OFFSET_CAPTURE, $this->charOffset)) {
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
            $this->offset += strlen($s);
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
        return $this->offset >= strlen($this->input);
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

    public function unscan() {
        throw new NotImplementedException();
    }

    public function inspect() {
        throw new NotImplementedException();
    }

    private function encoding(): string {
        return $this->encoding ?: Environment::ENCODING;
    }
}