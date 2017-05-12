<?php
namespace Morpho\System;

use Morpho\Base\Must;
use Morpho\Core\Module as BaseModule;
use Morpho\Db\Sql\Db;
use Morpho\Error\ErrorHandler;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\BadRequestException;
use Morpho\Web\NotFoundException;
use Morpho\Web\Response;

class Module extends BaseModule {
    const NAME = 'morpho-os/system';

    const BAD_REQUEST_ERROR    = 'badRequest';
    const ACCESS_DENIED_ERROR  = 'accessDenied';
    const NOT_FOUND_ERROR      = 'notFound';
    const UNCAUGHT_ERROR       = 'uncaughtError';

    const BAD_REQUEST_ERROR_HANDLER    = 'badRequestHandler';
    const ACCESS_DENIED_ERROR_HANDLER  = 'accessDeniedHandler';
    const NOT_FOUND_ERROR_HANDLER      = 'notFoundHandler';
    const UNCAUGHT_ERROR_HANDLER       = 'uncaughtErrorHandler';

    private $thrownExceptions = [];

    public function install(Db $db) {

    }

    /**
     * @Listen afterDispatch 100
     */
    public function afterDispatch(array $event) {
        /*
        $headers = $event[1]['request']->getResponse()->getHeaders();
        $headers->addHeaderLine('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
            ->addHeaderLine('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->addHeaderLine('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->addHeaderLine('Cache-Control', 'post-check=0, pre-check=0')
            ->addHeaderLine('Pragma', 'no-cache');
        */
    }

    public static function defaultErrorHandler(string $errorType): array {
        Must::contain([self::NOT_FOUND_ERROR, self::ACCESS_DENIED_ERROR, self::BAD_REQUEST_ERROR, self::UNCAUGHT_ERROR], $errorType);
        return [self::NAME, 'Error', $errorType];
    }

    /**
     * @Listen dispatchError 100
     */
    public function dispatchError(array $event) {
        $exception = $event[1]['exception'];
        $request = $event[1]['request'];

        $handleError = function (string $errorType, int $statusCode, bool $logError) use ($request, $exception) {
            $serviceManager = $this->serviceManager;

            if ($logError) {
                $serviceManager->get('errorLogger')
                    ->emergency($exception, ['exception' => $exception]);
            }

            if ($serviceManager->get('site')->config()['throwDispatchErrors']) {
                throw $exception;
            }

            $handler = $serviceManager->get('settingManager')
                ->get($errorType . 'Handler', self::NAME);
            if (false === $handler) {
                $handler = static::defaultErrorHandler($errorType);
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
            $handleError(self::NOT_FOUND_ERROR, Response::STATUS_CODE_404, false);
        } elseif ($exception instanceof AccessDeniedException) {
            $handleError(self::ACCESS_DENIED_ERROR, Response::STATUS_CODE_403, false);
        } elseif ($exception instanceof BadRequestException) {
            $handleError(self::BAD_REQUEST_ERROR, Response::STATUS_CODE_400, false);
        } else {
            $handleError(self::UNCAUGHT_ERROR, Response::STATUS_CODE_500, true);
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
                    'id' => [
                        'type' => 'primaryKey',
                    ],
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
}
