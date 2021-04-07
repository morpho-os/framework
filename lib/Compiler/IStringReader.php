<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler;

/**
 * String reader can be useful in recursrive descent parsers.
 * Based on [stringscanner from ruby](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html), see [license](https://github.com/ruby/ruby/blob/master/COPYING)
 */
interface IStringReader {
    /**
     * Sets the new input. Modifies the offset. Modifies the `matched` register.
     * Ruby method: [string=()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-string-3D).
     * @param string $input The new input string.
     */
    public function setInput(string $input): void;

    /**
     * Ruby method: [string()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-string).
     * @return string The current input string.
     */
    public function input(): string;

    /**
     * Appends $input to the current input. Doesn't advance offset. Doesn't modify `matched` register.
     * @param string $input
     */
    public function concat(string $input): void;

    /**
     * Sets the new offset.
     * Ruby method: [pos=()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-pos-3D).
     * @param int $offset The new offset.
     */
    public function setOffset(int $offset): void;

    /**
     * Return the current offset.
     * Ruby method: [pos()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-pos).
     * @return int The current offset.
     */
    public function offset(): int;

    /**
     * Reads the text (input) matching the pattern. Can advance or not the offset. Modifies the `matched` register.
     * Ruby methods:
     *     [scan()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-scan).
     *     [scan_full()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-scan_full)
     * @param string $re Pattern (PCRE) to match.
     * @param bool $advanceOffset If true the offset will be advanced.
     * @param bool $returnStr
     *     If true then string will be returned if there is a match, if there is no match the null will be returned.
     *     If false then int will be returned if there is a match, if there is no match the null will be returned.
     * @return string|int|null Depending from the $advanceOffset and $returnStr arguments the different result will be returned.
     */
    public function read(string $re, bool $advanceOffset = true, bool $returnStr = true): string|int|null;

    /**
     * Checks what `read()` will read. Does not advance offset. Modifies the `matched` register. Shortcut for the `read($re, false, true)`.
     * @param string $re Pattern (PCRE) to match.
     * @return string|null The matched substring or null if there is no match.
     */
    public function check(string $re): ?string;

    /**
     * Skips the matching bytes from the current offset. Advances the offset. Modifies the `matched register. Shortcut for the `read($re, true, false)`
     * @param string $re Pattern (PCRE) to match.
     * @return int|null Number of matched bytes or null in case of no matching.
     */
    public function skip(string $re): ?int;

    /**
     * Returns the number of matching bytes from the current offset. Doesn't advances the offset. Modifies the `matched` register. Shortcut for the `read($re, false, false)`.
     * Ruby method: [match?()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-match-3F)
     * @param string $re Pattern (PCRE) to match.
     * @return int|null
     */
    public function look(string $re): ?int;

    /**
     * Reads the text until the pattern is matched. Can advance or not the offset. Modifies the `matched` register.
     * Ruby methods:
     *     [scan_until()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-scan_until).
     *     [search_full()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-search_full).
     * @param string $re Pattern (PCRE) to match.
     * @param bool $advanceOffset If true the offset will be advanced.
     * @param bool $returnStr
     *     If true then string will be returned if there is a match, if there is no match the null will be returned.
     *     If false then int will be returned if there is a match, if there is no match the null will be returned.
     * @return string|int|null Depending from the $advanceOffset and $returnStr arguments the different result will be returned.
     */
    public function readUntil(string $re, bool $advanceOffset = true, bool $returnStr = true): string|int|null;

    /**
     * Checks what `readUntil()` will read. Does not advance offset. Modifies the `matched` register. Shortcut for the `readUntil($re, false, true)`.
     * @param string $re Pattern (PCRE) to match.
     * @return string|null The matched substring from the current offset up to and including the end of the match or null otherwise.
     */
    public function checkUntil(string $re): ?string;

    /**
     * Skips the text until the pattern is matched. Advances the offset. Modifes the `matched` register. Shortcut for the `readUntil($re, true, false)`.
     * @param string $re Pattern (PCRE) to match.
     * @return int|null
     */
    public function skipUntil(string $re): ?int;

    /**
     * Looks ahead to see if the pattern exists anywhere in the string. Doesn't advance the offset. Modifies the `matched` register. Shortcut for the `readUntil($re, false, false)`.
     * Ruby method: [exist?()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-exist-3F).
     * @param string $re Pattern (PCRE) to match.
     * @return int|null
     */
    public function lookUntil(string $re): ?int;

    /**
     * Reads the next byte (ASCII char). Advances the offset. Modifies the `matched` register.
     * Ruby method: [get_byte()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-get_byte).
     * @return string|null
     */
    public function readByte(): ?string;

    /**
     * Changes the offset to the previous one. Only one previous offset is remembered. Resets the `matched` register to null.
     * Ruby method: [unscan()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-unscan).
     * @return void
     */
    public function unread(): void;





    /**
     * Returns a string with length $n from the current offset. Doesn't advance the offset. Does not modify the `matched` register.
     * @param int $n
     * @return string
     */
    public function peek(int $n): string;















    public function terminate(): void;

    public function reset(): void;








    //public function charOffset(): int;

    /**
     * Ruby method: [beginning_of_line()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-beginning_of_line-3F).
     * @return bool `true` if the offset is at the beginning of a line.
     */
    public function isBol(): bool;

    /**
     * [eos()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-eos-3F)
     * @return bool `true` if the offset is at the end of the string.
     */
    public function isEnd(): bool;












    /**
     * Returns the `matched` register: the last matched by PCRE string.
     * @return string|null
     */
    public function matched(): ?string;

    /**
     * [matched_size()](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html#method-i-matched_size) in Ruby.
     * @return int|null
     */
    public function matchedLen(): ?int;


    public function preMatch(): ?string;

    public function postMatch(): ?string;



    public function rest(): string;

    /*
    //public function isAnchored();
    public function captures();
    public function size();

    public function restLen();

    public function valuesAt();
    */
}
