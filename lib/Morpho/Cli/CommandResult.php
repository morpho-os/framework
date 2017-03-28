<?php
declare(strict_types = 1);

namespace Morpho\Cli;

use const Morpho\Base\EOL_REGEXP;
use Morpho\Base\NotImplementedException;

class CommandResult {
    protected $exitCode;
    protected $stdout;

    public function __construct(string $command, int $exitCode, ?string $stdout) {
        $this->command = $command;
        $this->exitCode = $exitCode;
        $this->stdout = $stdout;
    }

    public function command(): string {
        return $this->command;
    }

    public function stdout(): string {
        throw new NotImplementedException(__METHOD__);
    }

    public function stderr(): string {
        throw new NotImplementedException(__METHOD__);
    }

    public function exitCode(): int {
        return $this->exitCode;
    }

    public function isError(): bool {
        return $this->exitCode() !== Environment::SUCCESS_CODE;
    }

    // @TODO: Unify with #152.
    public function lines(bool $noEmptyLines = true, bool $trimLines = true, int $offset = 0, int $length = null): iterable {
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