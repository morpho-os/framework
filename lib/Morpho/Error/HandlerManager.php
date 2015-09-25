<?php
namespace Morpho\Error;

/**
 * Utility class to manage error and exception handlers.
 */
class HandlerManager {
    const ERROR = 'error';
    const EXCEPTION = 'exception';

    public static function callCurrent($handlerType, $args = array()) {
        $handler = self::getCurrent($handlerType);
        if (null !== $handler) {
            call_user_func_array($handler, $args);
        }
    }

    /**
     * @param $handlerType
     * @param callable $callback
     * @return bool
     */
    public static function isRegistered($handlerType, callable $callback) {
        return in_array($callback, self::getAll($handlerType));
    }

    public static function register($handlerType, callable $callback) {
        self::checkType($handlerType);
        $previousHandler = call_user_func('set_' . $handlerType . '_handler', $callback);

        return $previousHandler;
    }

    /**
     * @param string $handlerType
     * @param callable|null $callback
     *     If null all handlers will be deleted. If callback
     *     was provided then all handlers before will be deleted that are
     *     above in the inner PHP stack of handlers.
     * @return void
     */
    public static function unregister($handlerType, callable $callback = null) {
        self::checkType($handlerType);

        $method = 'restore_' . $handlerType . '_handler';

        do {
            $handler = self::getCurrent($handlerType);
            $method();
        } while ($handler && $handler !== $callback);
    }

    public static function getAllExceptionHandlers() {
        return self::getAll(self::EXCEPTION);
    }

    public static function getAllErrorHandlers() {
        return self::getAll(self::ERROR);
    }

    public static function getAll($handlerType) {
        self::checkType($handlerType);

        $unregisterMethod = 'restore_' . $handlerType . '_handler';
        $registerMethod = 'set_' . $handlerType . '_handler';

        $handlers = array();

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
    public static function getCurrent($handlerType) {
        self::checkType($handlerType);

        $currentHandler = call_user_func('set_' . $handlerType . '_handler', array(__CLASS__, __FUNCTION__));
        call_user_func('restore_' . $handlerType . '_handler');

        return $currentHandler;
    }

    private static function checkType($handlerType) {
        if (!in_array($handlerType, array(self::ERROR, self::EXCEPTION))) {
            throw new \InvalidArgumentException("Invalid handler type was provided '$handlerType'.");
        }
    }
}
