<?php
declare(strict_types=1);

namespace Morpho\Cli;

use function Morpho\Base\writeLn;

function escapeEachArg(array $args): array {
    return array_map('escapeshellarg', $args);
}

function cmd(string $cmd/*, \Closure $args = null*/): CommandResult {
    ob_start();
    passthru(
        $cmd,// . (null !== $args ? ' ' . implode(' ', $args()) : ''),
        $exitCode
    );
    return new CommandResult(trim(ob_get_clean()), $exitCode);
}

function cmdJson(string $cmd, array $args = null): string {
    return json_decode(
        cmd($cmd, $args),
        true
    );
}

function askYesNo($question) {
    echo $question . "? (y/n): ";
    do {
        $answer = trim(fgets(STDIN));
        if ($answer === 'y' || $answer === 'Y') {
            return true;
        } elseif ($answer === 'n' || $answer === 'N') {
            return false;
        } else {
            writeLn("Please answer: y, Y, n, N");
        }
    } while (true);
}