<?php
declare(strict_types = 1);

namespace Morpho\Cli;

use Morpho\Base\ArrayTool;
use function Morpho\Base\{
    writeLn, buffer
};
use Morpho\Base\NotImplementedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

function writeOk() {
    writeLn("OK");
}

function writeError(string $string, bool $exit = true) {
    fwrite(STDERR, $string);
    if ($exit) {
        exit(Environment::FAILURE_CODE);
    }
}

function writeErrorLn(string $string, bool $exit = true) {
    writeError($string . "\n", $exit);
}

function colorize(string $text, $code): string {
    // @TODO:
    // RGB
    // $fg: 38;05;$codes
    // $bg: 48;05;$codes
    // $codes: $code(';' $code)?
    // $code: int in [1..255]
    // $flags $fg $bg
    // $flags: $bold | $italic | $underline | $inverse | $blink
    // $bold: 01
    // $italic: 03
    // $underline: 04
    // $inverse: 07
    // $blink: 05
    /*
    $flags
    00=none
    01=bold
    04=underscore
    05=blink
    07=reverse
    08=concealed
    */

    // \033 is ASCII-code of the ESC.
    static $colorOn = "\033[";
    static $colorOff = "\033[0m";
    return $colorOn . implode(';', (array)$code) . 'm'   // prefix
        . str_replace([']', '['], ['\\]', '\\['], $text) // text with escaped '[', ']'
        . $colorOff;                                     // suffix
}

function escapeArg($arg): string {
    return ProcessUtils::escapeArgument($arg);
}

function escapeArgs(array $args): array {
    return array_map(__NAMESPACE__ . '\\escapeArg', $args);
}

function cmd($command, array $options = null): CommandResult {
    $options = ArrayTool::handleOptions((array) $options, [
        'checkExitCode' => true,
    ]);
    $process = new Process($command);
    $process->run();
    if ($options['checkExitCode']) {
        // @TODO
    }
    $result = new CommandResult($process);
    return $result;
    /*
    $options = ArrayTool::handleOptions((array) $options, [
        'stdIn' => null,
        'stdOut' => null,
        'stdErr' => null,
/*
        'showStdOut' => false,

        'stdOut' => null,
        'stdOut' => null,
        'stdErr' => null,
* /
        'showStdOut' => false,
        'returnStdOut' => true,
        'showStdErr' => true,
        'returnStdErr' => false,
        'checkExitCode' => true,
    ]);
    */
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

    $result = new CommandResult('', 0);
    if ($options['checkExitCode'] && $result->wasError()) {
        throw new Exception((string)$result, $result->exitCode());
    }
    return $result;
*/
}

function cmdSu(string $command, array $options = null): CommandResult {
    throw new NotImplementedException();
    //return cmd('sudo bash -c "' . $cmd . '"', $args, $options);
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
            writeLn("Invalid choice, please type y or n");
        }
    } while (true);
}
/*
function ask(string $question): string {
    echo $question;
    return strtolower(trim(fgets(STDIN)));
}*/
