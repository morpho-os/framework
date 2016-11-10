<?php
declare(strict_types = 1);

namespace Morpho\Cli;

class CommandResult {
    protected $exitCode, $output;

    public function __construct($exitCode, $output) {
        $this->exitCode = $exitCode;
        $this->output = $output;
    }

    public function notSuccess(): bool {
        return $this->exitCode() !== Environment::SUCCESS_CODE;
    }

    public function error(): CommandError {
        throw new \NotImplementedException();
    }

    public function exitCode(): int {
        return $this->exitCode;
    }

    public function __toString(): string {
        return $this->output;
    }
}

class CommandError {

}
