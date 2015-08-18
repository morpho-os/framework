<?php
namespace MorphoTest\Web\View;

use Morpho\Di\ServiceManager;
use Morpho\Test\TestCase;
use Morpho\Web\Messenger;
use Morpho\Web\Messenger\IMessageStorage;
use Morpho\Web\View\MessengerPlugin;
use Morpho\Base\ArrayIterator;

class MessengerPluginTest extends TestCase {
    public function setUp() {
        $this->messenger = new Messenger();
        $this->messenger->setMessageStorage(new MessageStorage());
        $serviceManager = new ServiceManager();
        $serviceManager->set('messenger', $this->messenger);

        $this->messengerPlugin = new MessengerPlugin();
        $this->messengerPlugin->setServiceManager($serviceManager);
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
        $this->assertHtmlEquals($expected, $this->messengerPlugin->renderPageMessages());
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
        $this->assertHtmlEquals($expected, $this->messengerPlugin->renderPageMessages());
    }
}

class MessageStorage extends ArrayIterator implements IMessageStorage {
}