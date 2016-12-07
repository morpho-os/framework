<?php
declare(strict_types = 1);

namespace Morpho\Cli;

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

    public function __toString(): string {
        return $this->stdout;
    }
}