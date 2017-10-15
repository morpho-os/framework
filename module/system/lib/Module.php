<?php
namespace Morpho\System;

use Morpho\Base\Must;
use const Morpho\Core\VENDOR;
use Morpho\Error\ErrorHandler;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\NotFoundException;
use Morpho\Web\Request;
use Morpho\Web\Response;
use Morpho\Web\Module as BaseModule;
use Morpho\Web\View\IHasTheme;
use Morpho\Web\View\THasTheme;

class Module extends BaseModule implements IHasTheme {
    use THasTheme;

    public const NAME = VENDOR . '/system';

    private $thrownExceptions = [];

    /**
     * @Listen dispatchError -9999
     */
    public function dispatchError($event) {
        $exception = $event->args['exception'];
        $request = $event->args['request'];

        $handleError = function (string $handlerName, int $statusCode, bool $logError) use ($request, $exception) {
            $serviceManager = $this->serviceManager;

            if ($logError) {
                $serviceManager->get('errorLogger')
                    ->emergency($exception, ['exception' => $exception]);
            }

            $settingsManager = $serviceManager->get('settingsManager');

            if ($settingsManager->get('throwDispatchErrors', self::NAME)) {
                throw $exception;
            }

            $handler = $settingsManager->getOrDefault($handlerName, self::NAME);
            if ($handler) {
                $errorHandler = $handler['handler'];
            } else {
                $errorHandler = static::defaultErrorHandler($handlerName);
            }

            foreach ($this->thrownExceptions as $prevException) {
                if (ErrorHandler::hashId($prevException) === ErrorHandler::hashId($exception)) {
                    throw new \RuntimeException('Exception loop has been detected', 0, $exception);
                }
            }
            $this->thrownExceptions[] = $exception;

            $request->setHandler($errorHandler)
                ->isDispatched(false);
            $request->setInternalParam('error', $exception);
            $request->response()->setStatusCode($statusCode);
        };

        if ($exception instanceof NotFoundException) {
            $handleError(Request::NOT_FOUND_ERROR_HANDLER, Response::STATUS_CODE_404, false);
        } elseif ($exception instanceof AccessDeniedException) {
            $handleError(Request::ACCESS_DENIED_ERROR_HANDLER, Response::STATUS_CODE_403, false);
        } elseif ($exception instanceof BadRequestException) {
            $handleError(Request::BAD_REQUEST_ERROR_HANDLER, Response::STATUS_CODE_400, false);
        } else {
            $handleError(Request::UNCAUGHT_ERROR_HANDLER, Response::STATUS_CODE_500, true);
        }
    }

    private static function defaultErrorHandler(string $handlerName): array {
        Must::contain([
            Request::NOT_FOUND_ERROR_HANDLER,
            Request::ACCESS_DENIED_ERROR_HANDLER,
            Request::BAD_REQUEST_ERROR_HANDLER,
            Request::UNCAUGHT_ERROR_HANDLER
        ], $handlerName);
        return [self::NAME, 'Error', str_replace('Handler', '', $handlerName)];
    }
}
