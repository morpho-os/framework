<?php
namespace MorphoTest\Error;

use Morpho\Error\ErrorHandler;
use Morpho\Error\HandlerManager;
use Morpho\Error\ExceptionEvent;

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

    public function testHandleErrorConvertsErrorToException() {
        $errorHandler = $this->createErrorHandler();
        $errorHandler->register();

        try {
            trigger_error("My message", E_USER_WARNING);
            $this->fail();
        } catch (\Morpho\Error\UserWarningException $ex) {
        }
        $this->assertEquals(__LINE__ - 4, $ex->getLine());
        $this->assertEquals("My message", $ex->getMessage());
        $this->assertEquals(__FILE__, $ex->getFile());
        $this->assertEquals(E_USER_WARNING, $ex->getSeverity());
    }

    public function testErrorSuppressOperator() {
        $errorHandler = $this->createErrorHandler();
        $errorHandler->register();

        $errorHandler->attach(function (\Exception $exception) {
            $this->fail();
        });

        @trigger_error("My message");
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
