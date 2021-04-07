<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Compiler;

/**
 * Based on [stringscanner from ruby](https://docs.ruby-lang.org/en/3.0.0/StringScanner.html), see [license](https://github.com/ruby/ruby/blob/master/COPYING)
 */
class ByteReader implements IStringReader {
    protected string $input;
    protected int $offset = 0;
    protected int $prevOffset = 0;
    protected ?string $matched = null;
    protected bool $anchored = true;

    /**
     * ByteReader constructor.
     * @param string $input
     * @param bool Either use the `A` PCRE modifier (PCRE_ANCHORED) for all regular expressions or not.
     */
    public function __construct(string $input, bool $anchored = true) {
        $this->input = $input;
        $this->anchored = $anchored;
    }

    public function setInput(string $input): void {
        $this->input = $input;
        $this->reset();
    }

    public function input(): string {
        return $this->input;
    }

    public function concat(string $input): void {
        $this->input .= $input;
    }

    public function setOffset(int $offset): void {
        $this->offset = $offset;
    }

    public function offset(): int {
        return $this->offset;
    }

    public function read(string $re, bool $advanceOffset = true, bool $returnStr = true): string|int|null {
        $matched = null;
        if (preg_match($this->re($re), $this->input, $match, 0, $this->offset)) {
            $matched = $match[0];
            if ($advanceOffset) {
                $this->prevOffset = $this->offset;
                $this->offset += strlen($matched);
            }
        }
        $this->matched = $matched;
        if ($returnStr) {
            return $matched;
        }
        return $matched === null ? null : strlen($matched);
    }

    public function check(string $re): ?string {
        return $this->read($re, false);
    }

    public function skip(string $re): ?int {
        return $this->read($re, true, false);
    }

    public function look(string $re): ?int {
        return $this->read($re, false, false);
    }

    public function readUntil(string $re, bool $advanceOffset = true, bool $returnStr = true): string|int|null {
        if (preg_match($this->re($re, false), $this->input, $match, PREG_OFFSET_CAPTURE, $this->offset)) {
            $res = substr($this->input, $this->offset, $match[0][1] - $this->offset + strlen($match[0][0]));
            if ($advanceOffset) {
                $this->prevOffset = $match[0][1];
                $this->offset += strlen($res);
            }
            $this->matched = $match[0][0];
            if ($returnStr) {
                return $res;
            }
            return strlen($res);
        }
        return $this->matched = null;
    }

    public function checkUntil(string $re): ?string {
        return $this->readUntil($re, false);
    }

    public function skipUntil(string $re): ?int {
        return $this->readUntil($re, true, false);
    }

    public function lookUntil(string $re): ?int {
        return $this->readUntil($re, false, false);
    }

    public function readByte(): ?string {
        $matched = null;
        if (isset($this->input[$this->offset])) {
            $this->prevOffset = $this->offset;
            $matched = $this->input[$this->offset++];
        }
        return $this->matched = $matched;
    }

    public function unread(): void {
        if (null === $this->matched) {
            throw new StringReaderException("Previous match record doesn't exist");
        }
        $this->matched = null;
        $this->offset = $this->prevOffset;
    }




    public function peek(int $n): string {
        $res = substr($this->input, $this->offset, $n);
        if (false !== $res) {
            return $res;
        }
        return '';
    }












    /**
     * Sets the scan pointer to the end of the string.
     */
    public function terminate(): void {
        $this->offset = strlen($this->input);
    }



    public function isBol(): bool {
        $n = strlen($this->input);
        if ($this->offset == 0) {
            return true;
        }
        return $this->offset < $n
            && ($this->input[$this->offset - 1] == "\n" // *nix
                || $this->input[$this->offset - 1] == "\r" // mac
                || ($n >= 2 && $this->input[$this->offset - 2] == "\r" && $this->input[$this->offset - 1] == "\n")); // win
    }

    public function isEnd(): bool {
        return $this->offset >= strlen($this->input);
    }

/*    public function charOffset(): int {
        // For ByteScanner offset is always charOffset.
        return $this->offset();
    }*/









    public function matched(): ?string {
        return $this->matched;
    }

    public function matchedLen(): ?int {
        return null === $this->matched || $this->offset >= strlen($this->input)
            ? null
            : strlen($this->matched);
    }

    public function preMatch(): ?string {
        return null === $this->matched
            ? null
            : substr($this->input, 0, $this->prevOffset);
    }

    public function postMatch(): ?string {
        return null === $this->matched
            ? null
            : substr($this->input, $this->offset);
    }

    public function reset(): void {
        $this->matched = null;
        $this->offset = $this->prevOffset = 0;
    }

    public function rest(): string {
        $res = substr($this->input, $this->offset);
        if (false === $res) {
            return '';
        }
        return $res;
    }

    protected function re(string $re, bool $anchored = null): string {
        if (null === $anchored) {
            return $this->anchored ? $re . 'A' : $re;
        }
        return $anchored ? $re . 'A' : $re;
    }
}
