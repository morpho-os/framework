<?php
declare(strict_types=1);

namespace Morpho\Code;
use Morpho\Base\Environment;

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
     * @var ?string
     */
    protected $encoding;

    public function __construct(string $s, string $encoding = null) {
        $this->input = $s;
        // Use the mb_list_encodings() to get list of encodings
        $this->encoding = $encoding;
    }

    // Advancing the Scan Pointer

    // Original: getch
    public function char(): ?string {
        if ($this->eos()) {
            return null;
        }
        $ch = mb_substr($this->input, $this->offset, 1, $this->encoding());
        $this->offset += mb_strlen($ch);
        if (false === $ch) {
            return null;
        }
        return $ch;
    }

    // Original: get_byte
    public function byte(): ?string {
        if ($this->eos()) {
            return null;
        }
        $ch = substr($this->input, $this->offset, 1);
        $this->offset += strlen($ch);
        if (false === $ch) {
            return null;
        }
        return $ch;
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

    public function scanUntil() {

    }

    public function scanFull() {

    }

    public function skip() {

    }

    public function skipUntil() {

    }

    public function searchFull() {

    }

    // Looking Ahead

    public function check() {

    }

    public function checkUntil() {

    }

    // Original: exist
    public function exists(): bool {

    }

    /**
     * Original: match?(pattern)
     * Tests whether the given pattern is matched from the current scan pointer. Returns the length of the match, or nil. The scan pointer is not advanced.
     */
    public function matchLength(): ?int {

    }

    public function peek() {

    }

    // Finding Where we Are

    public function beginningOfLine(): bool {

    }

    public function eos(): bool {
        return $this->offset >= strlen($this->input);
    }

    public function rest() {

    }

    public function restSize() {

    }

    // Original: charpos
    public function charOffset() {
        $encoding = $this->encoding();
        return mb_strlen(mb_substr($this->input, 0, $this->offset, $encoding), $encoding);
    }

    // Original: pos
    public function offset(): int {
        return $this->offset;
    }

    // Setting Where we Are

    public function reset() {

    }

    public function terminate() {

    }

    // Original: pos=
    public function setPos() {

    }

    // Match Data

    /**
     * Original: matched()
     * Returns the last matched string.
     */
    public function lastMatched(): string {
    }

    public function matched(): bool {

    }

    // []
    public function matchedSubgroup(int $i) {

    }

    /**
     * Returns the size of the most recent match (see matched), or nil if there was no recent match.
     */
    public function matchedSize() {

    }

    public function preMatch() {

    }

    public function postMatch() {

    }

    // Miscellaneous

    // Original: <<
    public function append() {

    }

    public function concat() {

    }

    // Original: string
    public function input() {

    }

    // Original: string=
    public function setInput() {

    }

    public function unscan() {

    }

    public function inspect() {

    }

    private function encoding(): string {
        return $this->encoding ?: Environment::ENCODING;
    }
}