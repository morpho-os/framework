<?php
declare(strict_types=1);

namespace Morpho\Cli;

use function Morpho\Base\{printLn, jsonDecode};
use Morpho\Base\NotImplementedException;

function printOk() {
    printLn("OK");
}

function printError(string $string) {
    fwrite(STDERR, $string);
}

function printErrorLn(string $string) {
    printError($string . "\n");
}

function escapeEachArg(array $args): array {
    return array_map('escapeshellarg', $args);
}

function argString(array $args): string {
    return implode(' ', escapeEachArg($args));
}

function args(): ArgsHandler {
    return new ArgsHandler();
}

/**
 * Runs command with additional check for error.
 */
function cmdEx(string $command, $args = null): CommandResult {
    $result = cmd($command, $args);
    if ($result->isError()) {
        throw new CliException((string) $result, $result->getExitCode());
    }
    return $result;
}

function cmd(string $command, array $args = null): CommandResult {
    ob_start();
    passthru(
        $command . (null !== $args ? ' ' . argString($args) : ''),
        $exitCode
    );
    return new CommandResult(trim(ob_get_clean()), $exitCode);
}

function cmdJson(string $cmd, array $args = null): string {
    return jsonDecode(cmd($cmd, $args));
}

function pipe(array $commands) {
    // @TODO:
    throw new NotImplementedException();
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
            printLn("Please answer: y, Y, n, N");
        }
    } while (true);
}
