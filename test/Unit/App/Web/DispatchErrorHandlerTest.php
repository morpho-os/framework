<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\App\Web;

use Monolog\Logger;
use Morpho\Ioc\ServiceManager;
use Morpho\Testing\TestCase;
use Morpho\App\Web\DispatchErrorHandler;
use Morpho\App\Web\Request;

class DispatchErrorHandlerTest extends TestCase {
    public function testHandleException_ThrowsExceptionWhenTheSameErrorOccursTwice() {
        $handler = ['morpho-os/system', 'SomeCtrl', 'foo'];
        $dispatchErrorHandler = new DispatchErrorHandler();
        $dispatchErrorHandler->setExceptionHandler($handler);
        $exception = new \RuntimeException();
        $this->checkHandlesTheSameErrorOccurredTwice($dispatchErrorHandler, $handler, $exception, 500, true);
    }

    public function testThrowErrorsAccessor() {
        $this->checkBoolAccessor([new DispatchErrorHandler(), 'throwErrors'], false);
    }

    public function testHandleException_MustRethrowExceptionIfThrowErrorsIsSet() {
        $exception = new \RuntimeException('Uncaught test');
        //yield [, true];
        $dispatchErrorHandler = new DispatchErrorHandler();
        $request = new Request();
        $request->isHandled(true);
        $exceptionMessage = $exception->getMessage();
        $dispatchErrorHandler->throwErrors(true);
        $serviceManager = $this->mkServiceManagerWithLogger(true, $exception, 1);
        $dispatchErrorHandler->setServiceManager($serviceManager);
        try {
            $dispatchErrorHandler->handleException($exception, $request);
            $this->fail('Must throw an exception');
        } catch (\RuntimeException $e) {
            $this->assertSame([], $request->handler());
            $this->assertSame($exception, $e);
            $this->assertSame($exceptionMessage, $e->getMessage());
            $this->assertTrue($request->isHandled()); // break the main loop
        }
    }

    private function checkHandlesTheSameErrorOccurredTwice(DispatchErrorHandler $dispatchErrorHandler, array $expectedHandler, \Throwable $exception, int $expectedStatusCode, bool $mustLogError) {
        $request = new Request();
        $request->isHandled(true);

        $serviceManager = $this->mkServiceManagerWithLogger($mustLogError, $exception, 2);

        $dispatchErrorHandler->setServiceManager($serviceManager);

        $dispatchErrorHandler->handleException($exception, $request);

        $this->assertFalse($request->isHandled());
        $this->assertEquals($expectedHandler, $request->handler());
        $this->assertEquals($exception, $request['error']);
        $this->assertEquals($expectedStatusCode, $request->response()->statusCode());

        try {
            $dispatchErrorHandler->handleException($exception, $request);
            $this->fail('Exception was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertEquals('Exception loop has been detected', $e->getMessage());
            $this->assertEquals($e->getPrevious(), $exception);
        }
    }

    private function mkServiceManagerWithLogger(bool $mustLogError, \Throwable $expectedException, int $expectedNumberOfCalls) {
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
}
