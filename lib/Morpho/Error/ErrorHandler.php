<?php
namespace Morpho\Error;

/**
 * ErrorHandler is main error/exception handler. It transforms errors to exceptions
 * and sends notification about exception to the attached subscribers.
 * Based on code and ideas found at:
 * @link https://github.com/DmitryKoterov/php_exceptionizer
 * @link https://github.com/DmitryKoterov/debug_errorhook
 */
class ErrorHandler extends ExceptionHandler implements IErrorHandler {
    private $exitOnFatalError = true;

    private $iniDisplayErrors = false;

    private $registerAsFatalErrorHandler = true;

    private $fatalErrorHandlerActive = false;

    private $oldIniDisplayErrors;

    public function register() {
        parent::register();

        $this->oldIniDisplayErrors = ini_set('display_errors', (int)$this->iniDisplayErrors);

        HandlerManager::register(HandlerManager::ERROR, array($this, 'handleError'));

        if ($this->registerAsFatalErrorHandler) {
            register_shutdown_function(array($this, 'handleFatalError'));
            $this->fatalErrorHandlerActive = true;
        }

        return $this;
    }

    public function unregister() {
        parent::unregister();

        ini_set('display_errors', $this->oldIniDisplayErrors);

        HandlerManager::unregister(HandlerManager::ERROR, array($this, 'handleError'));

        // There is no unregister_shutdown_function(), so we emulate it via flag.
        $this->fatalErrorHandlerActive = false;
    }

    public function handleError($severity, $message, $filePath, $line, $context) {
        if ($severity & error_reporting()) {
            throw self::errorToException($severity, $message, $filePath, $line, $context);
        }
    }

    /**
     * @TODO: Can it be deleted?
     */
    public function handleFatalError() {
        $error = error_get_last();
        if ($this->fatalErrorHandlerActive
            && $error
            && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $this->handleException(
                self::errorToException($error['type'], $error['message'], $error['file'], $error['line'], null)
            );
            if ($this->exitOnFatalError) {
                exit();
            }
        }
    }

    /**
     * @param bool|null $flag
     * @return bool
     */
    public function registerAsFatalErrorHandler($flag = null) {
        if (null !== $flag) {
            $this->registerAsFatalErrorHandler = (bool)$flag;
        }
        return $this->registerAsFatalErrorHandler;
    }

    /**
     * @param bool|null $flag
     * @return bool
     */
    public function exitOnFatalError($flag = null) {
        if (null !== $flag) {
            $this->exitOnFatalError = (bool)$flag;
        }
        return $this->exitOnFatalError;
    }

    public static function errorToException($severity, $message, $filePath, $line, $context) {
        $class = self::getExceptionClass($severity);
        return new $class($message, 0, $severity, $filePath, $line);
    }

    private static function getExceptionClass($severity) {
        $levels = array(
            E_ERROR => 'ErrorException',
            E_WARNING => 'WarningException',
            E_PARSE => 'ParseException',
            E_NOTICE => 'NoticeException',
            E_CORE_ERROR => 'CoreErrorException',
            E_CORE_WARNING => 'CoreWarningException',
            E_COMPILE_ERROR => 'CompileErrorException',
            E_COMPILE_WARNING => 'CompileWarningException',
            E_USER_ERROR => 'UserErrorException',
            E_USER_WARNING => 'UserWarningException',
            E_USER_NOTICE => 'UserNoticeException',
            E_STRICT => 'StrictException',
            E_RECOVERABLE_ERROR => 'RecoverableErrorException',
            E_DEPRECATED => 'DeprecatedException',
            E_USER_DEPRECATED => 'UserDeprecatedException',
        );
        $class = __NAMESPACE__ . '\\' . $levels[$severity];

        return $class;
    }
}
