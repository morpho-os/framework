<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

use const Morpho\Base\EOL_FULL_RE;

abstract class CommandResult implements ICommandResult {
    /**
     * @var int
     */
    protected $exitCode;

    public function __construct(int $exitCode) {
        $this->exitCode = $exitCode;
    }

    public function exitCode(): int {
        return $this->exitCode;
    }

    public function isError(): bool {
        return $this->exitCode() !== Environment::SUCCESS_CODE;
    }

    public function getIterator() {
        return $this->lines();
    }

    // @TODO: Unify with #152.
    public function lines($noEmptyLines = true, bool $trimLines = true): iterable {
        /*if (!is_bool($noEmptyLines) && is_callable($noEmptyLines)) {
            $filter = $noEmptyLines;
        }*/
        foreach (\preg_split(EOL_FULL_RE, $this->out(), -1, $noEmptyLines ? PREG_SPLIT_NO_EMPTY : 0) as $line) {
            if ($trimLines) {
                $line = \trim($line);
            }
            if ($noEmptyLines && $line === '') {
                continue;
            }
            yield $line;
        }
    }

    public function __toString(): string {
        return (string) $this->out();
    }
}
