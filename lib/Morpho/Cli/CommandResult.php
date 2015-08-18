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

    public function isSuccessful(): bool {
        return $this->exitCode === 0;
    }

    public function __toString(): string {
        return $this->result;
    }
}