<?php
namespace MorphoTest\Web\View;

use Morpho\Test\TestCase;
use Morpho\Web\View\IMessageStorage;
use Morpho\Web\View\MessengerPlugin;
use Morpho\Base\ArrayIterator;

class MessengerPluginTest extends TestCase {
    public function setUp() {
        $this->messenger = new MessengerPlugin();
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

    public function testRenderPageMessagesWithoutEscaping() {
        $this->messenger->addWarningMessage("<strong>Important</strong> warning.");
        $expected = <<<OUT
<div id="page-messages">
    <div class="messages warning">
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div class="alert-body">
                <strong>Important</strong> warning.
            </div>
        </div>
    </div>
</div>
OUT;
        $actual = $this->messenger->renderPageMessages();
        $this->assertHtmlEquals($expected, $actual);
    }

    public function testRenderPageMessagesWithEscaping() {
        $this->messenger->addWarningMessage("<div>Random {0} warning {1} has been occurred.</div>", ...['<b>system</b>', '<div>for <b>unknown</b> reason</div>']);
        $expected = <<<OUT
<div id="page-messages">
    <div class="messages warning">
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div class="alert-body">
                <div>Random &lt;b&gt;system&lt;/b&gt; warning &lt;div&gt;for &lt;b&gt;unknown&lt;/b&gt; reason&lt;/div&gt; has been occurred.</div>
            </div>
        </div>
    </div>
</div>
OUT;
        $actual = $this->messenger->renderPageMessages();
        $this->assertHtmlEquals($expected, $actual);
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
                MessengerPlugin::SUCCESS => [
                    [
                        'message' => 'Hello {0} and welcome',
                        'args' => ['<b>Name</b>'],
                    ],
                ],
                MessengerPlugin::WARNING => [
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