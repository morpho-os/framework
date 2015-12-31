<?php
namespace Morpho\Error;

class PhpErrorException extends \ErrorException {
    public function __toString() {
        $s = parent::__toString();
        $severity = $this->getSeverity();
        $levels = [
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_STRICT            => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        ];

        return preg_replace('/^(' . preg_quote(get_class($this), '/') . ')/si', '\1 (' . $levels[$severity] . ')', $s);
    }
}

class CoreErrorException extends PhpErrorException {
}

class CoreWarningException extends CoreErrorException {
}

class CompileErrorException extends CoreErrorException {
}

class CompileWarningException extends CompileErrorException {
}

class ErrorException extends CoreErrorException {
}

class RecoverableErrorException extends ErrorException {
}

class ParseException extends RecoverableErrorException {
}

class WarningException extends ParseException {
}

class NoticeException extends WarningException {
}

class StrictException extends NoticeException {
}

class DeprecatedException extends StrictException {
}

class UserErrorException extends ErrorException {
}

class UserWarningException extends UserErrorException {
}

class UserNoticeException extends UserWarningException {
}

class UserDeprecatedException extends UserNoticeException {
}
