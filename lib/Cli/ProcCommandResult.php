<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Cli;

use Symfony\Component\Process\Process;

class ProcCommandResult extends CommandResult {
    /**
     * @var Process
     */
    protected $proc;

    public function __construct(Process $proc, int $exitCode) {
        parent::__construct($exitCode);
        $this->proc = $proc;
    }

    public function command(): string {
        return $this->proc->getCommandLine();
    }

    public function stdOut(): string {
        return $this->proc->getOutput();
    }

    public function stdErr(): string {
        return $this->proc->getErrorOutput();
    }
}