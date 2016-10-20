<?php
declare(strict_types = 1);

namespace Morpho\Cli;

use Morpho\Base\ArrayTool;
use function Morpho\Base\{
    writeLn, buffer
};
use Morpho\Base\NotImplementedException;

function writeOk() {
    writeLn("OK");
}

function writeError(string $string, bool $exit = true) {
    fwrite(STDERR, $string);
    if ($exit) {
        exit(Environment::FAILURE_CODE);
    }
}

function colorize($text, $color) {
    throw new NotImplementedException();
}

function writeErrorLn(string $string, bool $exit = true) {
    writeError($string . "\n", $exit);
}

function escapeArgs(array $args): array {
    return array_map('escapeshellarg', $args);
}

function cmd(string $command, array $args = null, array $options = null): CommandResult {
    $options = ArrayTool::handleOptions((array) $options, [
        /*
        'stdIn' => null,
        'stdOut' => null,
        'stdErr' => null,
        */
        'showStdOut' => false,
        'returnStdOut' => true,
        'showStdErr' => true,
        'returnStdErr' => false,
        'checkExitCode' => true,
    ]);
    /*
    $runCmd = function () use ($command, $args, &$exitCode)/*: void * / {
        passthru(
            $command . (null !== $args ? ' ' . implode(' ', escapeArgs($args)) : ''),
            $exitCode
        );
    };
    if ($options['returnStdOut'] || (!$options['returnStdOut'] && !$options['showStdOut'])) {
        if ($options['returnStdOut']) {
            // 1, *
            $res = trim(buffer($runCmd));
            if ($options['showStdOut']) {
                // 1, 1
                echo $res;
            }
            $result = new CommandResult($res, $exitCode);
        } else {
            // 0, 0
            buffer($runCmd);
            $result = new CommandResult('', $exitCode);
        }
    } else {
        // 0, 1
        $runCmd();
        $result = new CommandResult('', $exitCode);
    }
    */
    if ($options['checkExitCode'] && $result->isError()) {
        throw new Exception((string)$result, $result->getExitCode());
    }
    return $result;
}

function cmdSu(string $cmd, array $args = null, array $options = null): CommandResult {
    return cmd('sudo bash -c "' . $cmd . '"', $args, $options);
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
