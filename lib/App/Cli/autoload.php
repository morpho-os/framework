<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Cli;

const STDIN_FD  = 0;
const STDOUT_FD = 1;
const STDERR_FD = 2;
const STD_PIPES = [
    STDIN_FD  => ['pipe', 'r'],  // child process will read from STDIN
    STDOUT_FD => ['pipe', 'w'],  // child process will write to STDOUT
    STDERR_FD => ['pipe', 'w'],  // child process will write to STDERR
];

use Morpho\Base\Config;
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
    exit(null !== $exitCode && 0 !== $exitCode ? $exitCode : Environment::FAILURE_CODE);
}

function errorLn(string $errMessage = null, int $exitCode = null): void {
    if ($errMessage) {
        showErrorLn($errMessage);
    }
    exit(null !== $exitCode && 0 !== $exitCode ? $exitCode : Environment::FAILURE_CODE);
}

function showError(string $errMessage): void {
    \fwrite(STDERR, $errMessage);
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
        . \implode(';', (array) $codes) . 'm'
        . $text
        . $colorOff;
}

function escapeArgs(iterable $args): array {
    $res = [];
    foreach ($args as $arg) {
        $res[] = \escapeshellarg($arg);
    }
    return $res;
}

function argsToStr($args): string {
    if (!\is_array($args)) {
        if (!$args instanceof \Traversable) {
            $args = (array)$args;
        }
    } else {
        if (!\count($args)) {
            return '';
        }
    }
    $suffix = \implode(' ', escapeArgs($args));
    return $suffix === '' ? '' : ' ' . $suffix;
}

function envVarsToStr(array $envVars): string {
    if (!\count($envVars)) {
        return '';
    }
    $str = '';
    foreach ($envVars as $name => $value) {
        if (!\preg_match('~^[a-z][a-z0-9_]*$~si', (string)$name)) {
            throw new \RuntimeException('Invalid variable name');
        }
        $str .= ' ' . $name . '=' . \escapeshellarg($value);
    }
    return \substr($str, 1);
}

function shell(string $command, array $config = null): ICommandResult {
/*    if (isset($config['capture'])) {
        if (!isset($config['show'])) {
            $config['show'] = !$config['capture'];
        }
    }*/
    $config = Config::check([
        'checkCode' => true,
        // @TODO: tee: buffer and display output
        'capture' => false,
        'show' => true,
        'envVars' => null,
    ], (array) $config);
    $output = '';
    $exitCode = 1;
    if ($config['envVars']) {
        $command = envVarsToStr($config['envVars']) . ';' . $command;
    }

    if ($config['capture']) {
        $output = capture(function () use ($command, &$exitCode) {
            \passthru($command, $exitCode);
        });
        if ($config['show']) {
            // Capture and show
            echo $output;
        }
    } else {
        if ($config['show']) {
            // Don't capture, but show
            \passthru($command, $exitCode);
        } else {
            // Don't capture, don't show => we are capturing to avoid displaying the result, but don't save the output.
            capture(function () use ($command, &$exitCode) {
                \passthru($command, $exitCode);
            });
        }
    }

    if ($config['checkCode']) {
        checkExitCode($exitCode);
    }
    // @TODO: Check the `system` function https://github.com/Gabriel439/Haskell-Turtle-Library/blob/master/src/Turtle/Bytes.hs#L319
    // @TODO: To get stderr use 2>&1 at the end.
    return new ShellCommandResult($command, $exitCode, $output, '');
}

function shellSu(string $command, array $config = null): ICommandResult {
    return shell('sudo bash -c "' . $command . '"', $config);
}

/**
 * Taken from https://habr.com/ru/post/135200/
 * @param string $cmd
 */
function shell1(string $cmd, $env = null) {
    $pid = pcntl_fork();
    if ($pid < 0) {
        throw new \RuntimeException('fork failed');
    }
    if ($pid == 0) {
        pcntl_exec('/bin/sh', ['-c', $cmd], $env ?? []); // @TODO: pass $_ENV?
        exit(127);
    }
    pcntl_waitpid($pid, $status);
    return pcntl_wexitstatus($status);
}

/**
 * @param array|string $command
 * @param array|null $config
 * @return ICommandResult
 */
function proc($command, array $config = null): ICommandResult {
    $config = Config::check([
        'checkCode' => true,
        // @TODO: tee: buffer and display output
        //'capture' => false, // @TODO
    ], (array) $config);
    $process = is_array($command) ? new Process($command) : Process::fromShellCommandline($command);
    $exitCode = $process->run();
    if ($config['checkCode']) {
        checkExitCode($exitCode);
    }
    return new ProcCommandResult($process, $exitCode);
}

function checkExitCode(int $exitCode, string $errMessage = null): int {
    if ($exitCode !== 0) {
        throw new \RuntimeException("Command returned non-zero exit code: " . (int) $exitCode . (null !== $errMessage ? '. ' . $errMessage : ''));
    }
    return $exitCode;
}

function checkResult(ICommandResult $result) {
    if ($result->isError()) {
        errorLn($result->stdErr() . ' Exit code: ' . $result->exitCode());
    }
}

function ask(string $question, bool $trim = true): string {
    echo $question;
    $result = \fgets(STDIN);
    // \fgets() returns false on Ctrl-D
    if (false === $result) {
        $result = '';
    }
    return $trim ? \trim($result) : $result;
}

function askYesNo(string $question): bool {
    echo $question . "? (y/n): ";
    do {
        $answer = \strtolower(\trim(\fgets(STDIN)));
        if ($answer === 'y') {
            return true;
        } elseif ($answer === 'n') {
            return false;
        } else {
            showLn("Invalid choice, please type y or n");
        }
    } while (true);
}
