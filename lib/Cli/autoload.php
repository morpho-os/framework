<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
declare(strict_types = 1);
namespace Morpho\Cli;

const STDIN_FD  = 0;
const STDOUT_FD = 1;
const STDERR_FD = 2;
const STD_PIPES = [
    STDIN_FD  => ['pipe', 'r'],  // child process will read from STDIN
    STDOUT_FD => ['pipe', 'w'],  // child process will write to STDOUT
    STDERR_FD => ['pipe', 'w'],  // child process will write to STDERR
];

use Morpho\Base\ArrayTool;
use function Morpho\Base\showLn;
use function Morpho\Base\capture;
use Morpho\Error\DumpListener;
use Morpho\Error\ErrorHandler;
use Symfony\Component\Process\Process;

function bootstrap(): void {
    (new Environment())->init();
    (new ErrorHandler([new DumpListener()]))->register();
}

function showOk(): void {
    showLn("OK");
}

function error(string $errMessage = null, int $exitCode = null): void {
    if ($errMessage) {
        showError($errMessage);
    }
    exit(null !== $exitCode ? $exitCode : Environment::FAILURE_CODE);
}

function errorLn(string $errMessage = null, int $exitCode = null): void {
    if ($errMessage) {
        showErrorLn($errMessage);
    }
    exit(null !== $exitCode ? $exitCode : Environment::FAILURE_CODE);
}

function showError(string $errMessage): void {
    fwrite(STDERR, $errMessage);
}

function showErrorLn(string $errMessage = null): void {
    showError($errMessage . "\n");
}

function stylize(string $text, $codes): string {
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
    return $colorOn
        . implode(';', (array) $codes) . 'm'
        . $text
        . $colorOff;
}

function escapeArgs(iterable $args): array {
    $res = [];
    foreach ($args as $arg) {
        $res[] = escapeshellarg($arg);
    }
    return $res;
}

function argsString($args): string {
    if (!is_array($args)) {
        if (!$args instanceof \Traversable) {
            $args = (array)$args;
        }
    } else {
        if (!count($args)) {
            return '';
        }
    }
    $suffix = implode(' ', escapeArgs($args));
    return $suffix === '' ? '' : ' ' . $suffix;
}

function shell(string $command, array $options = null): ICommandResult {
    $options = ArrayTool::handleOptions((array) $options, [
        'checkExit' => true,
        // @TODO: tee: buffer and display output
        'capture' => false,
    ]);
    $output = '';
    $exitCode = 1;
    if (!$options['capture']) {
        passthru($command, $exitCode);
    } else {
        $output = capture(function () use ($command, &$exitCode) {
            passthru($command, $exitCode);
        });
    }
    if ($options['checkExit']) {
        checkExit($exitCode);
    }
    // @TODO: Check the `system` function https://github.com/Gabriel439/Haskell-Turtle-Library/blob/master/src/Turtle/Bytes.hs#L319
    // @TODO: How to get stdErr?
    return new ShellCommandResult($command, $exitCode, $output, $output);
}

// @TODO: See \Composer\Util\ProcessExecutor
function proc(string $command, array $options = null): ICommandResult {
    $options = ArrayTool::handleOptions((array) $options, [
        'checkExit' => true,
        // @TODO: tee: buffer and display output
        //'capture' => false, // @TODO
    ]);
    $process = new Process($command);
    $exitCode = $process->run();
    if ($options['checkExit']) {
        checkExit($exitCode);
    }
    return new ProcCommandResult($process, $exitCode);
}

function shellSu(string $command, array $options = null): ICommandResult {
    return shell('sudo bash -c "' . $command . '"', $options);
}

function checkExit(int $exitCode): int {
    if ($exitCode !== 0) {
        throw new \RuntimeException("Command returned non-zero exit code: " . (int)$exitCode);
    }
    return $exitCode;
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
            showLn("Invalid choice, please type y or n");
        }
    } while (true);
}
