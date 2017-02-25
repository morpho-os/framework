<?php
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
use Morpho\Base\NotImplementedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

function showOk() {
    showLn("OK");
}

function error(string $errMessage = null) {
    if ($errMessage) {
        showError($errMessage);
    }
    exit(Environment::FAILURE_CODE);
}

function showError(string $errMessage) {
    fwrite(STDERR, $errMessage);
}

function showErrorLn(string $errMessage = null) {
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
    return $colorOn . implode(';', (array) $codes) . 'm'   // prefix
        . $text                                            // text
        . $colorOff;                                       // suffix
}

function escapeArg($arg): string {
    return ProcessUtils::escapeArgument($arg);
}

function escapeArgs(array $args): array {
    return array_map(__NAMESPACE__ . '\\escapeArg', $args);
}

function argsString(array $args): string {
    if (!count($args)) {
        return '';
    }
    return implode(' ', escapeArgs($args));
}

function cmd($command, array $options = null): CommandResult {
    $options = ArrayTool::handleOptions((array) $options, [
        'checkExitCode' => true,
        'shell' => true,
        // @TODO: tee: buffer and display output
        'buffer' => false,
    ]);
    if (PHP_SAPI !== 'cli') {
        throw new NotImplementedException();
    }

    $output = null;
    $exitCode = 1;
    if ($options['shell']) {
        if (!$options['buffer']) {
            // @TODO: How to return $output?
            passthru($command, $exitCode);
        } else {
            $output = \Morpho\Base\buffer(function () use ($command, &$exitCode) {
                passthru($command, $exitCode);
            });
        }
    } else {
        $process = new Process($command);
        $process->run();
//        $process->setTimeout(null);
        $exitCode = $process->getExitCode();

        throw new NotImplementedException();

        // @TODO: handle $options['buffer'] and handler $output.
        $output = $process->getOutput();
    }

    if ($options['checkExitCode']) {
        checkExitCode($exitCode);
    }
    return new CommandResult($command, $exitCode, $output);
}

function cmdSu(string $command, array $options = null): CommandResult {
    return cmd('sudo bash -c "' . $command . '"', $options);
}

function checkExitCode(int $exitCode): int {
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

function download(string $uri, string $outFilePath = null): string {
    // @TODO: use curl, wget or fetch, see the `man parallel`
    throw new NotImplementedException();
    return $outFilePath;
}
