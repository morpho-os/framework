<?php
namespace Morpho\System;

use Morpho\Base\Must;
use const Morpho\Core\VENDOR;
use Morpho\Db\Sql\Db;
use Morpho\Error\ErrorHandler;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\NotFoundException;
use Morpho\Web\Request;
use Morpho\Web\Response;
use Morpho\Web\Module as BaseModule;
use Morpho\Web\View\IWithThemeModule;
use Morpho\Web\View\TWithThemeModule;

class Module extends BaseModule implements IWithThemeModule {
    use TWithThemeModule;

    public const NAME = VENDOR . '/system';

    private $thrownExceptions = [];

    public function install(Db $db) {

    }

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

            if ($serviceManager->get('site')->config()['throwDispatchErrors']) {
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
            $handleError(REQUEST::BAD_REQUEST_ERROR_HANDLER, Response::STATUS_CODE_400, false);
        } else {
            $handleError(REQUEST::UNCAUGHT_ERROR_HANDLER, Response::STATUS_CODE_500, true);
        }
    }

    public static function tableDefinitions(): array {
        return [
            /*
            'file' => [
                'columns' => [
                    'id' => [
                        'type' => 'primaryKey',
                    ],
                    'path' => [
                        'type' => 'varchar',
                    ],
                    'type' => [
                        'type' => 'varchar',
                        'length' => 10,
                    ],
                ],
                'indexes' => [
                    'path',
                    'type',
                ],
            ],
            */
            'module' => [
                'columns' => [
                    'id' => [
                        'type' => 'primaryKey'
                    ],
                    'name' => [
                        'type' => 'varchar',
                    ],
                    'status' => [
                        'type' => 'int',
                    ],
                    'weight' => [
                        'type' => 'int',
                    ],
                ],
                'uniqueKeys' => ['name'],
            ],
            //'controller' =>
            'event' => [
                'columns' => [
                    'name' => [
                        'type' => 'varchar',
                    ],
                    'priority' => [
                        'type' => 'integer',
                    ],
                    'method' => [
                        'type' => 'varchar',
                    ],
                    'moduleId' => [
                        'type' => 'integer',
                        'unsigned' => true,
                    ],
                ],
                'primaryKey' => [
                    'columns' => [
                        'name',
                        'moduleId',
                    ],
                ],
                'foreignKeys' => [
                    [
                        'childColumn' => 'moduleId',
                        'parentTable' => 'module',
                        'parentColumn' => 'id',
                    ],
                ],
            ],
            'setting' => [
                'columns' => [
                    'name' => [
                        'type' => 'varchar',
                    ],
                    'value' => [
                        'type' => 'text',
                    ],
                    'moduleId' => [
                        'type' => 'int',
                        'unsigned' => 'true',
                    ],
                ],
                'primaryKey' => [
                    'columns' => [
                        'name',
                        'moduleId',
                    ],
                ],
                'foreignKeys' => [
                    [
                        'childColumn' => 'moduleId',
                        'parentTable' => 'module',
                        'parentColumn' => 'id',
                    ]
                ],
            ],
        ];
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
