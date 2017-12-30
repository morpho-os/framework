<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Error;

/**
 * Utility class to manage error and exception handlers.
 */
class HandlerManager {
    public const ERROR = 'error';
    public const EXCEPTION = 'exception';

    public static function isHandlerRegistered(string $handlerType, callable $callback): bool {
        return in_array($callback, self::handlersOfType($handlerType));
    }

    /**
     * @return callable|null
     */
    public static function registerHandler(string $handlerType, callable $callback) {
        if ($handlerType === self::ERROR) {
            return set_error_handler($callback);
        } elseif ($handlerType === self::EXCEPTION) {
            return set_exception_handler($callback);
        }
        self::invalidHandlerTypeException($handlerType);
    }

    /**
     * @param callable|null $callback
     *     If null all handlers will be deleted. If callback
     *     was provided then all handlers before will be deleted that are
     *     above in the inner PHP stack of handlers.
     */
    public static function unregisterHandler(string $handlerType, callable $fn = null) {
        self::checkHandlerType($handlerType);
        if (null === $fn) {
            // Restore default error handler
            ('set_' . $handlerType . '_handler')(null);
        } else {
            $popHandler = 'restore_' . $handlerType . '_handler';
            $handlers = [];
            $found = false;
            /** @noinspection PhpAssignmentInConditionInspection */
            while ($handler = self::handlerOfType($handlerType)) {
                $popHandler();
                if ($handler === $fn) {
                    $found = true;
                    break;
                } else {
                    $handlers[] = $handler;
                }
            }
            $pushHandler = 'set_' . $handlerType . '_handler';
            foreach (array_reverse($handlers) as $handler) {
                $pushHandler($handler);
            }
            if (!$found) {
                throw new \RuntimeException('Unable to unregister the ' . $handlerType . ' handler');
            }
        }
    }

    public static function popHandlersUntil(string $handlerType, callable $predicate) {
        self::checkHandlerType($handlerType);
        $popHandler = 'restore_' . $handlerType . '_handler';
        /** @noinspection PhpAssignmentInConditionInspection */
        while ($currentHandler = self::handlerOfType($handlerType)) {
            if ($predicate($currentHandler)) {
                return;
            }
            $popHandler();
        }
    }

    public static function exceptionHandler() {
        return self::handlerOfType(self::EXCEPTION);
    }

    public static function errorHandler() {
        return self::handlerOfType(self::ERROR);
    }

    public static function exceptionHandlers() {
        return self::handlersOfType(self::EXCEPTION);
    }

    public static function errorHandlers() {
        return self::handlersOfType(self::ERROR);
    }

    public static function handlersOfType(string $handlerType) {
        self::checkHandlerType($handlerType);

        $popHandler = 'restore_' . $handlerType . '_handler';
        $pushHandler = 'set_' . $handlerType . '_handler';

        $handlers = [];
        do {
            $handler = self::handlerOfType($handlerType);
            $popHandler();
            if (!$handler) {
                break;
            }
            $handlers[] = $handler;
        } while ($handler);

        $handlers = array_reverse($handlers);

        // Restore handlers back.
        foreach ($handlers as $handler) {
            $pushHandler($handler);
        }

        return $handlers;
    }

    /**
     * @param string $handlerType
     * @return callable|null
     */
    public static function handlerOfType(string $handlerType) {
        self::checkHandlerType($handlerType);

        $currentHandler = call_user_func('set_' . $handlerType . '_handler', [__CLASS__, __FUNCTION__]);
        call_user_func('restore_' . $handlerType . '_handler');

        return $currentHandler;
    }

    private static function checkHandlerType(string $handlerType) {
        if (!in_array($handlerType, [self::ERROR, self::EXCEPTION], true)) {
            self::invalidHandlerTypeException($handlerType);
        }
    }

    private static function invalidHandlerTypeException(string $handlerType) {
        throw new \InvalidArgumentException("Invalid handler type was provided '$handlerType'.");
    }
}
