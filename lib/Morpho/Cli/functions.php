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
use function Morpho\Base\writeLn;
use Morpho\Base\NotImplementedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

function writeOk() {
    writeLn("OK");
}

function error(string $errMessage = null) {
    if ($errMessage) {
        writeError($errMessage);
    }
    exit(Environment::FAILURE_CODE);
}

function writeError(string $errMessage) {
    fwrite(STDERR, $errMessage);
}

function writeErrorLn(string $errMessage) {
    writeError($errMessage . "\n");
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
    ]);
    if (PHP_SAPI !== 'cli') {
        throw new NotImplementedException();
    }

    $exitCode = 1;
    if ($options['shell']) {
        $output = \Morpho\Base\buffer(function () use ($command, &$exitCode) {
            passthru($command, $exitCode);
        });
    } else {
        $process = new Process($command);
        $process->run();
        $exitCode = $process->getExitCode();
        $output = $process->getOutput();
    }

    if ($options['checkExitCode'] && $exitCode !== 0) {
        throw new \RuntimeException("Command returned non-zero exit code: " . $exitCode);
    }

    return new CommandResult($command, $exitCode, $output);
}

function cmdSu(string $command, array $options = null): CommandResult {
    return cmd('sudo bash -c "' . $command . '"', $options);
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

function download(string $uri, string $outFilePath = null): string {
    // @TODO: use curl or wget
    throw new NotImplementedException();
    return $outFilePath;
}