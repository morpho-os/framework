<?php
namespace Morpho\Error;

/**
 * Utility class to manage error and exception handlers.
 */
class HandlerManager {
    const ERROR = 'error';
    const EXCEPTION = 'exception';

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
    public static function unregisterHandler(string $handlerType, callable $callback = null) {
        self::checkHandlerType($handlerType);

        $method = 'restore_' . $handlerType . '_handler';

        do {
            $handler = self::handlerOfType($handlerType);
            $method();
        } while ($handler && $handler !== $callback);
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

        $unregisterMethod = 'restore_' . $handlerType . '_handler';
        $registerMethod = 'set_' . $handlerType . '_handler';

        $handlers = [];

        do {
            $handler = self::handlerOfType($handlerType);
            $unregisterMethod();
            if (!$handler) {
                break;
            }
            $handlers[] = $handler;
        } while ($handler);

        // Restore handlers back.
        foreach ($handlers as $handler) {
            $registerMethod($handler);
        }

        return array_reverse($handlers);
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
