<?php
declare(strict_types = 1);

namespace Morpho\Cli;

use Symfony\Component\Process\Process;

class CommandResult {
    private $process;

    public function __construct(Process $process) {
        $this->process = $process;
    }

    public function wasError(): bool {
        return $this->exitCode() !== Environment::SUCCESS_CODE;
    }

    public function exitCode(): int {
        $exitCode = $this->process->getExitCode();
        if (null === $exitCode) {
            throw new \RuntimeException("Process is not terminated yet");
        }
        return $exitCode;
    }

    public function __toString(): string {
        return $this->process->getOutput();
    }
}