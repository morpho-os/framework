<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web;

use Morpho\Base\Must;
use const Morpho\App\Core\VENDOR;
use Morpho\Ioc\IHasServiceManager;
use Morpho\Ioc\THasServiceManager;
use Morpho\Error\ErrorHandler;

class DispatchErrorHandler implements IHasServiceManager {
    use THasServiceManager;

    public const ACCESS_DENIED_ERROR = 'accessDenied';
    public const BAD_REQUEST_ERROR   = 'badRequest';
    public const NOT_FOUND_ERROR     = 'notFound';
    public const UNCAUGHT_ERROR      = 'uncaught';

    private $thrownExceptions = [];

    private $throwErrors = false;

    private $handlers = [];

    public function throwErrors(bool $flag = null): bool {
        if (null !== $flag) {
            $this->throwErrors = $flag;
        }
        return $this->throwErrors;
    }

    public function setHandler(string $errorType, array $handler): void {
        Must::contain([
            self::NOT_FOUND_ERROR,
            self::ACCESS_DENIED_ERROR,
            self::BAD_REQUEST_ERROR,
            self::UNCAUGHT_ERROR,
        ], $errorType);
        $this->handlers[$errorType] = $handler;
    }

    public function handleError(\Throwable $exception, Request $request) {
        if ($exception instanceof NotFoundException) {
            $params = [self::NOT_FOUND_ERROR, Response::NOT_FOUND_STATUS_CODE, false];
        } elseif ($exception instanceof AccessDeniedException) {
            $params = [self::ACCESS_DENIED_ERROR, Response::FORBIDDEN_STATUS_CODE, false];
        } elseif ($exception instanceof BadRequestException) {
            $params = [self::BAD_REQUEST_ERROR, Response::BAD_REQUEST_STATUS_CODE, false];
        } else {
            $params = [self::UNCAUGHT_ERROR, Response::INTERNAL_SERVER_ERROR_STATUS_CODE, true];
        }
        [$errorType, $httpStatusCode, $logError] = $params;

        if ($logError) {
            $this->logError($exception);
        }

        if ($this->throwErrors) {
            throw $exception;
        }

        $handler = $this->handlers[$errorType] ?? $this->defaultErrorHandler($errorType);

        foreach ($this->thrownExceptions as $prevException) {
            if (ErrorHandler::hashId($prevException) === ErrorHandler::hashId($exception)) {
                throw new \RuntimeException('Exception loop has been detected', 0, $exception);
            }
        }
        $this->thrownExceptions[] = $exception;

        $request->setHandler($handler);
        $request->isDispatched(false);
        $request['error'] = $exception;
        $request->response()->setStatusCode($httpStatusCode);
    }

    private function defaultErrorHandler(string $errorType): array {
        return [VENDOR . '/system', 'Error', str_replace('Handler', '', $errorType)];
    }

    protected function logError($exception): void {
        $errorLogger = $this->serviceManager['errorLogger'];
        $errorLogger->emergency($exception, ['exception' => $exception]);
    }
}
