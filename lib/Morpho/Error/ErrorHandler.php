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

    private $registerAsFatalErrorHandler = true;

    private $fatalErrorHandlerActive = false;

    private $oldIniSettings = null;

    public function register() {
        parent::register();

        HandlerManager::register(HandlerManager::ERROR, [$this, 'handleError']);

        if ($this->registerAsFatalErrorHandler) {
            register_shutdown_function([$this, 'handleFatalError']);
            $this->fatalErrorHandlerActive = true;
        }

        $this->setIniSettings();

        return $this;
    }

    public function unregister() {
        parent::unregister();

        HandlerManager::unregister(HandlerManager::ERROR, [$this, 'handleError']);

        // There is no unregister_shutdown_function(), so we emulate it via flag.
        $this->fatalErrorHandlerActive = false;

        $this->restoreIniSettings();
    }

    public function handleError($severity, $message, $filePath, $lineNo, $context) {
        if ($severity & error_reporting()) {
            throw self::errorToException($severity, $message, $filePath, $lineNo, $context);
        }
    }

    /**
     * @TODO: Can it be deleted?
     */
    public function handleFatalError() {
        $error = error_get_last();
        if ($this->fatalErrorHandlerActive
            && $error
            && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])
        ) {
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
     */
    public function registerAsFatalErrorHandler($flag = null): bool {
        if (null !== $flag) {
            $this->registerAsFatalErrorHandler = (bool) $flag;
        }
        return $this->registerAsFatalErrorHandler;
    }

    /**
     * @param bool|null $flag
     */
    public function exitOnFatalError($flag = null): bool {
        if (null !== $flag) {
            $this->exitOnFatalError = (bool) $flag;
        }
        return $this->exitOnFatalError;
    }

    public static function errorToException($severity, $message, $filePath, $lineNo, $context): \ErrorException {
        $class = self::getExceptionClass($severity);
        return new $class($message, 0, $severity, $filePath, $lineNo);
    }

    protected function setIniSettings() {
        $oldIniSettings = [];
        $oldIniSettings['display_errors'] = ini_set('display_errors', 0);
        // @TODO: Do we need set the 'display_startup_errors'?
        $oldIniSettings['display_startup_errors'] = ini_set('display_startup_errors', 0);
        $this->oldIniSettings = $oldIniSettings;
    }

    protected function restoreIniSettings() {
        if (null === $this->oldIniSettings) {
            return;
        }
        ini_set('display_errors', $this->oldIniSettings['display_errors']);
        ini_set('display_startup_errors', $this->oldIniSettings['display_startup_errors']);
    }

    protected static function getExceptionClass($severity): string {
        $levels = [
            E_ERROR             => 'ErrorException',
            E_WARNING           => 'WarningException',
            E_PARSE             => 'ParseException',
            E_NOTICE            => 'NoticeException',
            E_CORE_ERROR        => 'CoreErrorException',
            E_CORE_WARNING      => 'CoreWarningException',
            E_COMPILE_ERROR     => 'CompileErrorException',
            E_COMPILE_WARNING   => 'CompileWarningException',
            E_USER_ERROR        => 'UserErrorException',
            E_USER_WARNING      => 'UserWarningException',
            E_USER_NOTICE       => 'UserNoticeException',
            E_STRICT            => 'StrictException',
            E_RECOVERABLE_ERROR => 'RecoverableErrorException',
            E_DEPRECATED        => 'DeprecatedException',
            E_USER_DEPRECATED   => 'UserDeprecatedException',
        ];
        return __NAMESPACE__ . '\\' . $levels[$severity];
    }
}
