<?php
namespace System;

use Morpho\Base\Assert;
use Morpho\Core\Module as BaseModule;
use Morpho\Db\Sql\Db;
use Morpho\Error\ErrorHandler;
use Morpho\Web\AccessDeniedException;
use Morpho\Web\NotFoundException;
use Morpho\Web\Response;

class Module extends BaseModule {
    const NAME = 'system';

    const ACCESS_DENIED_ERROR  = 'accessDenied';
    const PAGE_NOT_FOUND_ERROR = 'pageNotFound';
    const UNCAUGHT_ERROR       = 'uncaughtError';

    const ACCESS_DENIED_ERROR_HANDLER  = 'accessDeniedHandler';
    const PAGE_NOT_FOUND_ERROR_HANDLER = 'pageNotFoundHandler';
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
        Assert::isOneOf($errorType, [self::ACCESS_DENIED_ERROR, self::PAGE_NOT_FOUND_ERROR, self::UNCAUGHT_ERROR]);
        return ['System', 'Error', $errorType];
    }

    /**
     * @Listen dispatchError 100
     */
    public function dispatchError(array $event) {
        $exception = $event[1]['exception'];
        $request = $event[1]['request'];
        $handleError = function (string $errorType, int $statusCode, bool $logError) use ($request, $exception) {
            if ($logError) {
                $this->serviceManager->get('logger')->emergency($exception, ['exception' => $exception]);
            }

            $handler = $this->serviceManager->get('settingManager')
                ->get($errorType . 'Handler', self::NAME);
            if (false === $handler) {
                $handler = static::defaultErrorHandler($errorType);
            }

            foreach ($this->thrownExceptions as $prevException) {
                if (ErrorHandler::getHashId($prevException) === ErrorHandler::getHashId($exception)) {
                    throw new \RuntimeException('Exception loop has been detected', 0, $exception);
                }
            }
            $this->thrownExceptions[] = $exception;

            $request->setHandler($handler)
                ->isDispatched(false);
            $request->setInternalParam('error', $exception);
            $request->getResponse()->setStatusCode($statusCode);
        };
        if ($exception instanceof AccessDeniedException) {
            $handleError(self::ACCESS_DENIED_ERROR, Response::STATUS_CODE_403, false);
        } elseif ($exception instanceof NotFoundException) {
            $handleError(self::PAGE_NOT_FOUND_ERROR, Response::STATUS_CODE_404, false);
        } else {
            $handleError(self::UNCAUGHT_ERROR, Response::STATUS_CODE_500, true);
        }
    }

    public static function getTableDefinitions(): array {
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
                'indexes' => [
                    'name',
                ],
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
