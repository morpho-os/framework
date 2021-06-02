<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Base;

use InvalidArgumentException;

/**
 * Based on https://github.com/php-fig/log/blob/master/Psr/Log/LoggerInterface.php
 */
interface ILogger {
    /**
     * System is unusable.
     */
    public function emergency(string $message, array $context = []): void;

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     */
    public function alert(string $message, array $context = []): void;

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public function critical(string $message, array $context = []): void;

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public function error(string $message, array $context = []): void;

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Normal but significant events.
     */
    public function notice(string $message, array $context = []): void;

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     */
    public function info(string $message, array $context = []): void;

    /**
     * Detailed debug information.
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Logs with an arbitrary level.
     * @throws InvalidArgumentException
     */
    public function log(int|string $level, string $message, array $context = []): void;
}