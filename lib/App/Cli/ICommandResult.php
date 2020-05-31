<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

interface ICommandResult extends \IteratorAggregate {
    public function command(): string;

    public function out(): string;

    public function err(): string;

    public function exitCode(): int;

    public function isError(): bool;

    public function lines(): iterable;

    public function __toString();
}
