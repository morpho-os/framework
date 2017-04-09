<?php
declare(strict_types = 1);

namespace Morpho\Cli;

use const Morpho\Base\EOL_REGEXP;

class CommandResult {
    /**
     * @var int
     */
    protected $exitCode;
    /**
     * @var null|string
     */
    protected $stdout;
    /**
     * @var null|string
     */
    private $stderr;

    public function __construct(string $command, int $exitCode, ?string $stdout, string $stderr = null) {
        $this->command = $command;
        $this->exitCode = $exitCode;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function command(): string {
        return $this->command;
    }

    public function stdout(): ?string {
        return $this->stdout;
    }

    public function stderr(): ?string {
        return $this->stderr;
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
        return (string) $this->stdout();
    }
}