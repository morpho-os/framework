<?php
declare(strict_types = 1);

namespace Morpho\Cli;

use function Morpho\Base\{
    writeLn, decodeJson
};
use Morpho\Base\NotImplementedException;

function writeOk() {
    writeLn("OK");
}

function writeError(string $string) {
    fwrite(STDERR, $string);
}

function writeErrorLn(string $string) {
    writeError($string . "\n");
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

function cmdSu(string $cmd): CommandResult {
    return cmd('sudo bash -c "' . $cmd . '"');
}

function cmd(string $command, array $args = null, bool $catchStdOut = true, bool $throwExOnError = true): CommandResult {
    if ($catchStdOut) {
        ob_start();
        passthru(
            $command . (null !== $args ? ' ' . argString($args) : ''),
            $exitCode
        );
        $result = new CommandResult(trim(ob_get_clean()), $exitCode);
    } else {
        passthru(
            $command . (null !== $args ? ' ' . argString($args) : ''),
            $exitCode
        );
        $result = new CommandResult('', $exitCode);
    }
    if ($throwExOnError && $result->isError()) {
        throw new Exception((string)$result, $result->getExitCode());
    }
    return $result;
}

function cmdJson(string $cmd, array $args = null): string {
    return decodeJson(cmd($cmd, $args));
}

function pipe(array $commands) {
    // @TODO:
    throw new NotImplementedException();
}

function askYesNo(string $question): bool {
    echo $question . "? (y/n): ";
    do {
        $answer = strtolower(trim(fgets(STDIN)));
        if ($answer === 'y') {
            return true;
        } elseif ($answer === 'n') {
            return false;
        } else {
            writeLn("Please answer: y, Y, n, N");
        }
    } while (true);
}

function ask(string $question): string {
    echo $question;
    return strtolower(trim(fgets(STDIN)));
}
