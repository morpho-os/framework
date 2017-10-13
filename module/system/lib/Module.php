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
use Morpho\Core\Module as BaseModule;
use Morpho\Web\View\IWithThemeModule;
use Morpho\Web\View\TWithThemeModule;

class Module extends BaseModule implements IWithThemeModule {
    use TWithThemeModule;

    public const NAME = VENDOR . '/system';

    private $thrownExceptions = [];

    /**
     * @Listen dispatchError -9999
     */
    public function dispatchError($event) {
        $exception = $event[1]['exception'];
        $request = $event[1]['request'];

        $handleError = function (string $handlerName, int $statusCode, bool $logError) use ($request, $exception) {
            $serviceManager = $this->serviceManager;

            if ($logError) {
                $serviceManager->get('errorLogger')
                    ->emergency($exception, ['exception' => $exception]);
            }

            $siteConfig = $serviceManager->get('site')->config();
            if ($siteConfig['modules'][self::NAME]['throwDispatchErrors'] ?? false) {
                throw $exception;
            }

            $handler = $serviceManager->get('settingsManager')
                ->get($handlerName, self::NAME);
            if (false !== $handler) {
                $handler = $handler['handler'];
            } else {
                $handler = static::defaultErrorHandler($handlerName);
            }

            foreach ($this->thrownExceptions as $prevException) {
                if (ErrorHandler::hashId($prevException) === ErrorHandler::hashId($exception)) {
                    throw new \RuntimeException('Exception loop has been detected', 0, $exception);
                }
            }
            $this->thrownExceptions[] = $exception;

            $request->setHandler($handler)
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
