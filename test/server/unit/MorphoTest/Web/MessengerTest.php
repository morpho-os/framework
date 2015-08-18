<?php
namespace MorphoTest\Web;

use Morpho\Test\TestCase;
use Morpho\Base\ArrayIterator;
use Morpho\Web\Messenger;
use Morpho\Web\Messenger\IMessageStorage;

class MessengerTest extends TestCase {
    public function setUp() {
        $this->messenger = new Messenger();
        $this->messenger->setMessageStorage(new MessageStorage([]));
    }

    public function testCount() {
        $this->assertInstanceOf('\Countable', $this->messenger);

        $this->assertEquals(0, count($this->messenger));

        $this->messenger->addErrorMessage("Unknown error has been occurred, please power-off of your machine");

        $this->assertEquals(1, count($this->messenger));

        $this->messenger->addWarningMessage("A new warning has been occurred again.");

        $this->assertEquals(2, count($this->messenger));

        $this->messenger->clearMessages();

        $this->assertEquals(0, count($this->messenger));
    }

    public function testHasMessages() {
        $this->assertFalse($this->messenger->hasErrorMessages());
        $this->messenger->addErrorMessage("Some error.");
        $this->assertTrue($this->messenger->hasErrorMessages());

        $this->assertFalse($this->messenger->hasWarningMessages());
        $this->messenger->addWarningMessage("Some error.");
        $this->assertTrue($this->messenger->hasWarningMessages());
    }

    public function testToArray() {
        $this->messenger->addSuccessMessage('Hello {0} and welcome', ...['<b>Name</b>']);
        $this->messenger->addWarningMessage('Bar');
        $this->assertEquals(
            [
                Messenger::SUCCESS => [
                    [
                        'message' => 'Hello {0} and welcome',
                        'args' => ['<b>Name</b>'],
                    ],
                ],
                Messenger::WARNING => [
                    [
                        'message' => 'Bar',
                        'args' => [],
                    ],
                ],
            ],
            $this->messenger->toArray()
        );
    }
}

class MessageStorage extends ArrayIterator implements IMessageStorage {
}