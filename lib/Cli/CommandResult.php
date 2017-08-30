<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types = 1);
namespace Morpho\Cli;
use const Morpho\Base\EOL_FULL_RE;

class CommandResult implements \IteratorAggregate {
    /**
     * @var int
     */
    protected $exitCode;
    /**
     * @var null|string
     */
    protected $stdOut;
    /**
     * @var null|string
     */
    private $stdErr;

    /**
     * @var string
     */
    private $command;

    public function __construct(string $command, int $exitCode, ?string $stdOut, string $stdErr = null) {
        $this->command = $command;
        $this->exitCode = $exitCode;
        $this->stdOut = $stdOut;
        $this->stdErr = $stdErr;
    }

    public function command(): string {
        return $this->command;
    }

    public function stdOut(): ?string {
        return $this->stdOut;
    }

    public function stdErr(): ?string {
        return $this->stdErr;
    }

    public function exitCode(): int {
        return $this->exitCode;
    }

    public function isError(): bool {
        return $this->exitCode() !== Environment::SUCCESS_CODE;
    }

    // @TODO: Unify with #152.
    public function lines(bool $noEmptyLines = true, bool $trimLines = true, int $offset = 0, int $length = null): iterable {
        foreach (preg_split(EOL_FULL_RE, $this->stdOut, -1, $noEmptyLines ? PREG_SPLIT_NO_EMPTY : 0) as $line) {
            if ($trimLines) {
                $line = trim($line);
            }
            if ($noEmptyLines && $line === '') {
                continue;
            }
            yield $line;
        }
    }

    public function getIterator() {
        return $this->lines();
    }

    public function __toString(): string {
        return (string) $this->stdOut();
    }
}