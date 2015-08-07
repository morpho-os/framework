<?php
/**
 * 'require'
 */
function requireFile($filePath) {
    return require $filePath;
}

function writeLnError(...$errors) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
}

function writeLnAndWaitAnyKey(...$messages) {
    writeLn(...$messages);
    waitAnyKey();
}

function pressAnyKeyToContinue(...$messages) {
    array_push($messages, "Press any key to continue...");
    writeLnAndWaitAnyKey(...$messages);
}

function waitAnyKey() {
    echo "@TODO: " . __FUNCTION__;
    //fgetc(STDIN);
}

function error($message) {
    writeLnError($message);
    exit(1);
}

function invalidArgumentError($argName) {
    error("Invalid argument: '$argName'");
}

function emptyRequiredArgumentError($argName) {
    $argName = implode(' | ', (array)$argName);
    error("The argument '$argName' is required but empty");
}

function getOptionalArg(array $args, $name) {
    return isset($args[$name]) ? $args[$name] : null;
}

function getRequiredArg(array $args, $name) {
    if (!isset($args[$name]) || $args[$name] === '') {
        emptyRequiredArgumentError($name);
    }
    return $args[$name];
}

function getBoolArg(array $args, $name) {
    return array_key_exists($name, $args);
}

/**
 * @param array $args
 * @return Closure
 */
function spaceSepLongArgs(array $args) {
    return longArgs($args, ' ');
}

/**
 * @param array $args
 * @param string $sep
 * @return Closure
 */
function longArgs(array $args, $sep = '=') {
    return function () use ($args, $sep) {
        $res = [];
        foreach ($args as $name => $value) {
            if (is_numeric($name)) {
                $res[] = '--' . $value;
            } else {
                $res[] = '--'
                    . $name
                    . $sep
                    . ($value instanceof \Closure ? $value() : escapeshellarg($value));
            }
        }
        return implode(' ', $res);
    };
}

/**
 * @param $value
 * @return Closure
function notEscapedArg($value)
 * {
 * return function () use ($value) {
 * return $value;
 * };
 * }
 */

/**
 * @param array $values
 * @return array
 */
function escapeEach(array $values) {
    return array_map('escapeshellarg', $values);
}

/**
 * @param string $cmd
 * @param ...$args The first argument can be either a Closure or an array,
 * @return string
 */
function cmd($cmd, ...$args) {
    if (count($args) > 0) {
        if (is_array($args[0])) {
            $args = implode(' ', escapeEach($args[0]));
        } elseif ($args[0] instanceof \Closure) {
            $args = $args[0]();
        } else {
            $args = implode(' ', escapeEach($args));
        }
    }
//d($cmd, $args);
    ob_start();
    passthru(
        $cmd . (empty($args) ? '' : ' ' . $args),
        $exitCode
    );
    $output = ob_get_clean();

    if ($exitCode !== 0) {
        exit(1);
    }

    return trim($output);
}

function cmdJson($cmd) {
    return json_decode(cmd($cmd), true);
}

/**
 * @TODO: Rewrite of this function.
 *
 * @param $cmd
 * @param int $waitSec
 * @param int $numberOfAttempts
 */
function tryCmd($cmd, $waitSec = 30, $numberOfAttempts = 5) {
    $wasUnsuccessful = false;
    for (; $numberOfAttempts > 0; $numberOfAttempts--) {
        passthru($cmd, $exitCode);
        if ($exitCode === 0) {
            if ($wasUnsuccessful) {
                writeLn("Success!");
            }
            return;
        } else {
            if ($numberOfAttempts > 1) {
                $wasUnsuccessful = true;
                writeLn("Attempt was unsuccessfull, waiting $waitSec seconds and trying again...");
                sleep($waitSec);
            } else {
                break;
            }
        }
    }
    throw new \RuntimeException("Number of attempts has reached");
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
