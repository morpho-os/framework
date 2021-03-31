<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Error;

use ErrorException;
use Morpho\Error\ErrorHandler;
use Morpho\Error\ExceptionHandler;
use Morpho\Error\HandlerManager;
use Morpho\Error\IErrorHandler;
use Morpho\Error\WarningException;
use RuntimeException;
use function ini_get;
use function ini_set;
use function trigger_error;

require_once __DIR__ . '/BaseErrorHandlerTest.php';

class ErrorHandlerTest extends BaseErrorHandlerTest {
    private $oldErrorLevel;

    public function setUp(): void {
        parent::setUp();
        $this->oldErrorLevel = ini_get('display_errors');
    }

    public function tearDown(): void {
        parent::tearDown();
        ini_set('display_errors', $this->oldErrorLevel);
    }

    public function testInterface() {
        $errorHandler = new ErrorHandler();
        $this->assertInstanceOf(ExceptionHandler::class, $errorHandler);
        $this->assertInstanceOf(IErrorHandler::class, $errorHandler);
    }

    public function testCheckError_ThrowsErrorExceptionWhenErrorGetLastIsSet() {
        @$undefVar;
        $this->expectException(WarningException::class, 'Undefined variable $undefVar');
        ErrorHandler::checkError(false, "Op failed");
    }
    
    public function testCheckError_ThrowsRuntimeExceptionWhenErrorGetLastIsNotSet() {
        $msg = 'Op failed';
        $this->expectException(RuntimeException::class, $msg);
        ErrorHandler::checkError(false, $msg);
    }

    public function testCheckError_DoesNotThrowExceptionWhenPredIsTrueAndNoError() {
        ErrorHandler::checkError(true); // this call should not throw an exception
        $this->markTestAsNotRisky();
    }

    public function testHashId_TheSameFileDifferentLines() {
        try {
            throw new RuntimeException();
        } catch (RuntimeException $e1) {

        }
        $hashId1 = ErrorHandler::hashId($e1);
        $this->assertNotEmpty($hashId1);
        $this->assertEquals($hashId1, ErrorHandler::hashId($e1));
        try {
            throw new RuntimeException();
        } catch (RuntimeException $e2) {

        }
        $hashId2 = ErrorHandler::hashId($e2);
        $this->assertNotEmpty($hashId2);
        $this->assertNotEquals($hashId1, $hashId2);
    }

    public function testRegisterTwiceThrowsException() {
        $errorHandler = $this->mkErrorHandler();
        $errorHandler->register();
        $this->expectException('\LogicException');
        $errorHandler->register();
    }

    public function testUnregisterWithoutRegisterThrowsException() {
        $errorHandler = $this->mkErrorHandler();
        $this->expectException('\LogicException');
        $errorHandler->unregister();
    }

    public function testRegisterAndUnregister() {
        $errorHandler = $this->mkErrorHandler();
        $oldDisplayErrors = ini_get('display_errors');
        $oldDisplayStartupErrors = ini_get('display_startup_errors');
        $this->assertNull($errorHandler->register());
        $expected = [$errorHandler, 'handleError'];
        $this->assertEquals($expected, HandlerManager::handlerOfType(HandlerManager::ERROR));
        $expected = [$errorHandler, 'handleException'];
        $this->assertEquals($expected, HandlerManager::handlerOfType(HandlerManager::EXCEPTION));
        $this->assertEquals(0, ini_get('display_errors'));
        $this->assertEquals(0, ini_get('display_startup_errors'));

        $errorHandler->unregister();
        $this->assertEquals($this->prevErrorHandler, HandlerManager::handlerOfType(HandlerManager::ERROR));
        $this->assertEquals($this->prevExceptionHandler, HandlerManager::handlerOfType(HandlerManager::EXCEPTION));
        $this->assertEquals($oldDisplayErrors, ini_get('display_errors'));
        $this->assertEquals($oldDisplayStartupErrors, ini_get('display_startup_errors'));
    }

    public function testRegisterAsFatalErrorHandler() {
        $this->checkBoolAccessor([$this->mkErrorHandler(false), 'registerAsFatalErrorHandler'], true);
    }

    public function testExitOnFatalError() {
        $this->checkBoolAccessor([$this->mkErrorHandler(false), 'exitOnFatalError'], true);
    }

    public function dataTestHandleError_ConvertsErrorToException() {
        return [
            [
                E_USER_ERROR,
                'UserErrorException',
            ],
            [
                E_USER_WARNING,
                'UserWarningException',
            ],
            [
                E_USER_NOTICE,
                'UserNoticeException',
            ],
            [
                E_USER_DEPRECATED,
                'UserDeprecatedException',
            ],
        ];
    }

    /**
     * @dataProvider dataTestHandleError_ConvertsErrorToException
     */
    public function testHandleError_ConvertsErrorToException($severity, $expectedErrorClass) {
        $errorHandler = $this->mkErrorHandler();
        $errorHandler->register();

        try {
            trigger_error("My message", $severity);
            $this->fail();
        } catch (ErrorException $ex) {
            $this->assertInstanceOf('Morpho\\Error\\' . $expectedErrorClass, $ex);
        }
        $this->assertEquals(__LINE__ - 5, $ex->getLine());
        $this->assertEquals("My message", $ex->getMessage());
        $this->assertEquals(__FILE__, $ex->getFile());
        $this->assertEquals($severity, $ex->getSeverity());
    }

    public function dataErrorToException() {
        return [
            [
                E_ERROR,
                'ErrorException',
            ],
            [
                E_WARNING,
                'WarningException',
            ],
            [
                E_PARSE,
                'ParseException',
            ],
            [
                E_NOTICE,
                'NoticeException',
            ],
            [
                E_CORE_ERROR,
                'CoreErrorException',
            ],
            [
                E_CORE_WARNING,
                'CoreWarningException',
            ],
            [
                E_COMPILE_ERROR,
                'CompileErrorException',
            ],
            [
                E_COMPILE_WARNING,
                'CompileWarningException',
            ],
            [
                E_USER_ERROR,
                'UserErrorException',
            ],
            [
                E_USER_WARNING,
                'UserWarningException',
            ],
            [
                E_USER_NOTICE,
                'UserNoticeException',
            ],
            [
                E_STRICT,
                'StrictException',
            ],
            [
                E_RECOVERABLE_ERROR,
                'RecoverableErrorException',
            ],
            [
                E_DEPRECATED,
                'DeprecatedException',
            ],
            [
                E_USER_DEPRECATED,
                'UserDeprecatedException',
            ],
        ];
    }

    /**
     * @dataProvider dataErrorToException
     */
    public function testErrorToException($severity, $class) {
        $message = 'some';
        $lineNo = __LINE__;
        $exception = ErrorHandler::errorToException($severity, $message, __FILE__, $lineNo, null);
        $this->assertInstanceOf('Morpho\\Error\\' . $class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(__FILE__, $exception->getFile());
        $this->assertEquals($lineNo, $exception->getLine());
    }

    private function mkErrorHandler($init = true) {
        $errorHandler = new ErrorHandler();
        if ($init) {
            $errorHandler->exitOnFatalError(false);
            $errorHandler->registerAsFatalErrorHandler(false);
        }
        return $errorHandler;
    }
}
