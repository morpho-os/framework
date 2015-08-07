<?php
namespace Morpho\Error;

class ErrorTool {
    public static function getExceptionClass($severity) {
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
        $class = '\\' . __NAMESPACE__ . '\\' . $levels[$severity];

        return $class;
    }

    public static function errorToException($severity, $message, $filePath, $line, $context) {
        $class = self::getExceptionClass($severity);
        return new $class($message, 0, $severity, $filePath, $line);
    }
}
