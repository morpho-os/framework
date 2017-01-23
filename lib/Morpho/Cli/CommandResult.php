<?php
declare(strict_types = 1);

namespace Morpho\Cli;

use const Morpho\Base\EOL_REGEXP;

class CommandResult {
    protected $exitCode, $stdout;

    public function __construct($command, $exitCode, $stdout) {
        $this->command = $command;
        $this->exitCode = $exitCode;
        $this->stdout = $stdout;
    }

    public function command(): string {
        return $this->command;
    }

    public function isError(): bool {
        return $this->exitCode() !== Environment::SUCCESS_CODE;
    }

    public function exitCode(): int {
        return $this->exitCode;
    }

    public function lines(bool $noEmptyLines = true, bool $trimLines = true): iterable {
        foreach (preg_split(EOL_REGEXP, $this->stdout, -1, $noEmptyLines ? PREG_SPLIT_NO_EMPTY : 0) as $line) {
            if ($trimLines) {
                $line = trim($line);
            }
            if ($noEmptyLines && $line === '') {
                continue;
            }
            yield $line;
        }
    }

    public function __toString(): string {
        return $this->stdout;
    }
}