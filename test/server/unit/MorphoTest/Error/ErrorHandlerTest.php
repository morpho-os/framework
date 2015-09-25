<?php
namespace MorphoTest\Error;

use Morpho\Error\ErrorHandler;
use Morpho\Error\HandlerManager;
use Morpho\Error\ExceptionEvent;
use Morpho\Error\IExceptionEventListener;

require_once __DIR__ . '/BaseErrorHandlerTest.php';

class ErrorHandlerTest extends BaseErrorHandlerTest {
    public function setUp() {
        parent::setUp();
        $this->oldErrorLevel = ini_get('display_errors');
    }

    public function tearDown() {
        parent::tearDown();
        ini_set('display_errors', $this->oldErrorLevel);
    }

    public function testRegisterTwiceThrowsException() {
        $errorHandler = $this->createErrorHandler();
        $errorHandler->register();
        $this->setExpectedException('\LogicException');
        $errorHandler->register();
    }

    public function testUnregisterWithoutRegisterThrowsException() {
        $errorHandler = $this->createErrorHandler();
        $this->setExpectedException('\LogicException');
        $errorHandler->unregister();
    }

    public function testRegisterAndUnregister() {
        $errorHandler = $this->createErrorHandler();
        $oldDisplayErrors = ini_get('display_errors');
        $this->assertInstanceOf('\Morpho\Error\ErrorHandler', $errorHandler->register());
        $expected = [$errorHandler, 'handleError'];
        $this->assertEquals($expected, HandlerManager::getCurrent(HandlerManager::ERROR));
        $expected = [$errorHandler, 'handleException'];
        $this->assertEquals($expected, HandlerManager::getCurrent(HandlerManager::EXCEPTION));
        $this->assertEquals(0, ini_get('display_errors'));

        $errorHandler->unregister();
        $this->assertEquals($this->prevErrorHandler, HandlerManager::getCurrent(HandlerManager::ERROR));
        $this->assertEquals($this->prevExceptionHandler, HandlerManager::getCurrent(HandlerManager::EXCEPTION));
        $this->assertEquals($oldDisplayErrors, ini_get('display_errors'));
    }

    public function testRegisterAsFatalErrorHandler() {
        $this->assertBoolAccessor([$this->createErrorHandler(false), 'registerAsFatalErrorHandler'], true);
    }

    public function testExitOnFatalError() {
        $this->assertBoolAccessor([$this->createErrorHandler(false), 'exitOnFatalError'], true);
    }

    public function dataForTestHandleError_ConvertsErrorToException() {
        return [
            [
                E_USER_ERROR,
                'UserErrorException'
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
     * @dataProvider dataForTestHandleError_ConvertsErrorToException
     */
    public function testHandleError_ConvertsErrorToException($severity, $expectedErrorClass) {
        $errorHandler = $this->createErrorHandler();
        $errorHandler->register();

        try {
            trigger_error("My message", $severity);
            $this->fail();
        } catch (\ErrorException $ex) {
            $this->assertInstanceOf('Morpho\\Error\\' . $expectedErrorClass, $ex);
        }
        $this->assertEquals(__LINE__ - 5, $ex->getLine());
        $this->assertEquals("My message", $ex->getMessage());
        $this->assertEquals(__FILE__, $ex->getFile());
        $this->assertEquals($severity, $ex->getSeverity());
    }

    public function dataForErrorToException() {
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
     * @dataProvider dataForErrorToException
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

    private function createErrorHandler($init = true) {
        $errorHandler = new ErrorHandler();
        if ($init) {
            $errorHandler->exitOnFatalError(false);
            $errorHandler->registerAsFatalErrorHandler(false);
        }
        return $errorHandler;
    }
}
