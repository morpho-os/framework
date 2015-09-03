<?php
declare(strict_types=1);

namespace Morpho\Cli;

class CommandResult {
    protected $result;

    protected $exitCode;

    public function __construct(string $result, int $exitCode) {
        $this->result = $result;
        $this->exitCode = $exitCode;
    }

    public function isError(): bool {
        return $this->exitCode !== Environment::SUCCESS_EXIT_CODE;
    }

    public function getExitCode(): int {
        return $this->exitCode;
    }

    public function __toString(): string {
        return $this->result;
    }
}