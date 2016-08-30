<?php
declare(strict_types = 1);

namespace Morpho\Cli;

use Morpho\Base\ArrayTool;
use function Morpho\Base\{
    writeLn, decodeJson, buffer
};
use Morpho\Base\NotImplementedException;

function writeOk() {
    writeLn("OK");
}

function writeError(string $string, bool $exit = true) {
    fwrite(STDERR, $string);
    if ($exit) {
        exit(Enviroment::FAILURE_CODE);
    }
}

function writeErrorLn(string $string, bool $exit = true) {
    writeError($string . "\n", $exit);
}

function escapedArgs(array $args): array {
    return array_map('escapeshellarg', $args);
}

function escapedArgsString(array $args): string {
    return implode(' ', escapedArgs($args));
}

function cmdSu(string $cmd): CommandResult {
    return cmd('sudo bash -c "' . $cmd . '"');
}

function cmd(string $command, array $args = null, array $options = []): CommandResult {
    $options = ArrayTool::handleOptions($options, [
        'showStdOut' => false,
        'returnStdOut' => true,
        //'showStdErr' => true,  // @TODO
        'throwException' => true,
    ]);
    $runCmd = function () use ($command, $args, &$exitCode)/*: void */ {
        passthru(
            $command . (null !== $args ? ' ' . escapedArgsString($args) : ''),
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
    if ($options['throwException'] && $result->isError()) {
        throw new Exception((string)$result, $result->getExitCode());
    }
    return $result;
}

function cmdJson(string $cmd, array $args = null, array $options = []): string {
    return decodeJson((string)cmd($cmd, $args));
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
