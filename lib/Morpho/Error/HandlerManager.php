<?php
namespace Morpho\Error;

/**
 * Utility class to manage error and exception handlers.
 */
class HandlerManager {
    const ERROR = 'error';
    const EXCEPTION = 'exception';

    /**
     * @param $handlerType
     * @param callable $callback
     * @return bool
     */
    public static function isRegistered(string $handlerType, callable $callback) {
        return in_array($callback, self::getAll($handlerType));
    }

    /**
     * @return callable|null
     */
    public static function register(string $handlerType, callable $callback) {
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
    public static function unregister(string $handlerType, callable $callback = null) {
        self::checkType($handlerType);

        $method = 'restore_' . $handlerType . '_handler';

        do {
            $handler = self::getCurrent($handlerType);
            $method();
        } while ($handler && $handler !== $callback);
    }

    public static function getExceptionHandler() {
        return self::getCurrent(self::EXCEPTION);
    }

    public static function getErrorHandler() {
        return self::getCurrent(self::ERROR);
    }

    public static function getAllExceptionHandlers() {
        return self::getAll(self::EXCEPTION);
    }

    public static function getAllErrorHandlers() {
        return self::getAll(self::ERROR);
    }

    public static function getAll(string $handlerType) {
        self::checkType($handlerType);

        $unregisterMethod = 'restore_' . $handlerType . '_handler';
        $registerMethod = 'set_' . $handlerType . '_handler';

        $handlers = [];

        do {
            $handler = self::getCurrent($handlerType);
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
    public static function getCurrent(string $handlerType) {
        self::checkType($handlerType);

        $currentHandler = call_user_func('set_' . $handlerType . '_handler', array(__CLASS__, __FUNCTION__));
        call_user_func('restore_' . $handlerType . '_handler');

        return $currentHandler;
    }

    private static function checkType(string $handlerType) {
        if (!in_array($handlerType, [self::ERROR, self::EXCEPTION], true)) {
            self::invalidHandlerTypeException($handlerType);
        }
    }

    private static function invalidHandlerTypeException(string $handlerType) {
        throw new \InvalidArgumentException("Invalid handler type was provided '$handlerType'.");
    }
}
