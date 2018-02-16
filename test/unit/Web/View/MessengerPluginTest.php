<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Test\Unit\Web\View;

use Morpho\Ioc\ServiceManager;
use Morpho\Testing\TestCase;
use Morpho\Web\Messages\Messenger;
use Morpho\Web\Messages\IMessageStorage;
use Morpho\Web\View\MessengerPlugin;
use Morpho\Base\ArrayIterator;

class MessengerPluginTest extends TestCase {
    /**
     * @var Messenger
     */
    private $messenger;
    /**
     * @var MessengerPlugin
     */
    private $messengerPlugin;

    public function setUp() {
        $this->messenger = new Messenger();
        $this->messenger->setMessageStorage(new MessageStorage());
        $serviceManager = new ServiceManager();
        $serviceManager['messenger'] = $this->messenger;

        $this->messengerPlugin = new MessengerPlugin();
        $this->messengerPlugin->setServiceManager($serviceManager);
    }

    public function testRenderPageMessages_EscapesTextWithoutArgs() {
        $this->messenger->addWarningMessage("<strong>Important</strong> warning.");

        $expected = <<<OUT
<div id="page-messages">
    <div class="messages warning">
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div class="alert-body">
                &lt;strong&gt;Important&lt;/strong&gt; warning.
            </div>
        </div>
    </div>
</div>
OUT;
        $this->assertHtmlEquals($expected, $this->messengerPlugin->renderPageMessages());
    }

    public function testRenderPageMessages_EscapesTextButDoesNotEscapeArgs() {
        $this->messenger->addWarningMessage('<div>Random {0} warning "!" {1} has been occurred.</div>', ['<b>system</b>', '<div>for <b>unknown</b> reason</div>']);
        $expected = <<<OUT
<div id="page-messages">
    <div class="messages warning">
        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <div class="alert-body">
                &lt;div&gt;Random <b>system</b> warning &quot;!&quot; <div>for <b>unknown</b> reason</div> has been occurred.&lt;/div&gt;
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