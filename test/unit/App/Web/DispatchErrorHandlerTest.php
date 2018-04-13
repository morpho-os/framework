<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Monolog\Logger;
use Morpho\Base\IFn;
use const Morpho\App\Core\VENDOR;
use Morpho\Ioc\ServiceManager;
use Morpho\Testing\TestCase;
use Morpho\App\Web\AccessDeniedException;
use Morpho\App\Web\BadRequestException;
use Morpho\App\Web\DispatchErrorHandler;
use Morpho\App\Web\NotFoundException;
use Morpho\App\Web\Request;
use Morpho\App\Web\Response;

class DispatchErrorHandlerTest extends TestCase {
    public function dataForHandleError_ThrowsExceptionWhenTheSameErrorOccursTwice() {
        return [
            [
                new AccessDeniedException(), DispatchErrorHandler::ACCESS_DENIED_ERROR, Response::FORBIDDEN_STATUS_CODE, false,
            ],
            [
                new NotFoundException(), DispatchErrorHandler::NOT_FOUND_ERROR, Response::NOT_FOUND_STATUS_CODE, false,
            ],
            [
                new BadRequestException(), DispatchErrorHandler::BAD_REQUEST_ERROR,Response::BAD_REQUEST_STATUS_CODE, false,
            ],
            [
                new \RuntimeException(), DispatchErrorHandler::UNCAUGHT_ERROR, Response::INTERNAL_SERVER_ERROR_STATUS_CODE, true,
            ],
        ];
    }

    /**
     * @dataProvider dataForHandleError_ThrowsExceptionWhenTheSameErrorOccursTwice
     */
    public function testHandleError_ThrowsExceptionWhenTheSameErrorOccursTwice(\Throwable $exception, string $errorType, int $expectedStatusCode, bool $mustLogError) {
        $handler = ['morpho-os/system', 'SomeCtrl', 'foo'];
        $dispatchErrorHandler = new DispatchErrorHandler();
        $dispatchErrorHandler->setHandler($errorType, $handler);
        $this->checkHandlesTheSameErrorOccurredTwice($dispatchErrorHandler, $handler, $exception, $expectedStatusCode, $mustLogError);
    }

    /**
     * @dataProvider dataForHandleError_ThrowsExceptionWhenTheSameErrorOccursTwice
     */
    public function testDefaultErrorHandler(\Throwable $exception, string $errorType, int $expectedStatusCode, bool $mustLogError) {
        $handler = [
            VENDOR . '/system',
            'Error',
            $errorType,
        ];
        $this->checkHandlesTheSameErrorOccurredTwice(new DispatchErrorHandler(), $handler, $exception, $expectedStatusCode, $mustLogError);
    }

    public function testSetThrowErrorsAccessor() {
        $this->checkBoolAccessor([new DispatchErrorHandler(), 'throwErrors'], false);
    }

    public function dataForEffectOfTheThrowErrorFlag() {
        yield [new AccessDeniedException('Access denied test'), false];
        yield [new NotFoundException('Not found test'), false];
        yield [new BadRequestException('Bad request test'), false];
        yield [new \RuntimeException('Uncaught test'), true];
    }

    /**
     * @dataProvider dataForEffectOfTheThrowErrorFlag
     */
    public function testEffectOfTheThrowErrorFlag(\Throwable $exception, bool $mustLogError) {
        $dispatchErrorHandler = new DispatchErrorHandler();
        $request = $this->newRequest();
        $request->isDispatched(true);
        $exceptionMessage = $exception->getMessage();
        $dispatchErrorHandler->throwErrors(true);
        $serviceManager = $this->newServiceManagerWithLogger($mustLogError, $exception, 1);
        $dispatchErrorHandler->setServiceManager($serviceManager);
        try {
            $dispatchErrorHandler->handleError($exception, $request);
            $this->fail('Must throw an exception');
        } catch (\RuntimeException $e) {
            $this->assertSame([null, null, null], $request->handler());
            $this->assertSame($exception, $e);
            $this->assertSame($exceptionMessage, $e->getMessage());
            $this->assertTrue($request->isDispatched()); // break the main loop
        }
    }

    private function checkHandlesTheSameErrorOccurredTwice(DispatchErrorHandler $dispatchErrorHandler, array $expectedHandler, \Throwable $exception, int $expectedStatusCode, bool $mustLogError) {
        $request = $this->newRequest();
        $request->isDispatched(true);

        $serviceManager = $this->newServiceManagerWithLogger($mustLogError, $exception, 2);

        $dispatchErrorHandler->setServiceManager($serviceManager);

        $dispatchErrorHandler->handleError($exception, $request);

        $this->assertFalse($request->isDispatched());
        $this->assertEquals($expectedHandler, $request->handler());
        $this->assertEquals($exception, $request['error']);
        $this->assertEquals($expectedStatusCode, $request->response()->statusCode());

        try {
            $dispatchErrorHandler->handleError($exception, $request);
            $this->fail('Exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Exception loop has been detected', $e->getMessage());
            $this->assertEquals($e->getPrevious(), $exception);
        }
    }

    private function newServiceManagerWithLogger(bool $mustLogError, \Throwable $expectedException, int $expectedNumberOfCalls) {
        $errorLogger = $this->createMock(Logger::class);
        if ($mustLogError) {
            $errorLogger->expects($this->exactly($expectedNumberOfCalls))
                ->method('emergency')
                ->with($this->equalTo($expectedException), $this->equalTo(['exception' => $expectedException]));
        } else {
            $errorLogger->expects($this->never())
                ->method('emergency');
        }

        $serviceManager = $this->createMock(ServiceManager::class);
        $serviceManager->expects($this->any())
            ->method('offsetGet')
            ->with('errorLogger')
            ->willReturn($errorLogger);
        return $serviceManager;
    }

    private function newRequest(array $serverVars = null) {
        return new Request(
            null,
            $serverVars,
            new class implements IFn { public function __invoke($value) {} }
        );
    }
}
