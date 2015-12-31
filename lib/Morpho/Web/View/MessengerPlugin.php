<?php
namespace Morpho\Web\View;

use function Morpho\Base\{
    dasherize, escapeHtml, filterStringArgs
};
use Morpho\Di\{
    IServiceManager, IServiceManagerAware
};

class MessengerPlugin implements \Countable, IServiceManagerAware {
    private $serviceManager;

    public function count() {
        return $this->getMessenger()->count();
    }

    public function __invoke() {
        return $this;
    }

    public function renderPageMessages() {
        $html = '';
        $messenger = $this->getMessenger();
        if ($this->count()) {
            $renderedMessages = [];
            foreach ($messenger->toArray() as $type => $messages) {
                $renderedMessages[] = $this->renderMessages($messages, $type);
            }
            $html = $this->wrapPageMessages(implode("\n", $renderedMessages));
        }
        $messenger->clearMessages();
        return $html;
    }

    public function setServiceManager(IServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
    }

    protected function wrapPageMessages($messages) {
        return '<div id="page-messages">' . $messages . '</div>';
    }

    protected function renderMessages(array $messages, $type) {
        $renderedMessages = [];
        foreach ($messages as $message) {
            $renderedMessages[] = $this->renderMessage($message, $type);
        }
        return $this->wrapMessages(implode("\n", $renderedMessages), $type);
    }

    protected function wrapMessages($messages, $type) {
        return '<div class="messages ' . dasherize($type) . '">'
        . $messages
        . '</div>';
    }

    protected function renderMessage(array $message, $type) {
        return $this->wrapMessage(
            filterStringArgs(
                $message['message'],
                $message['args'],
                function ($value) { return nl2br(escapeHtml($value)); }
            ),
            $type
        );
    }

    protected function wrapMessage($message, $type) {
        return '<div class="alert alert-' . dasherize($type) . '">'
        . '<button type="button" class="close" data-dismiss="alert">&times;</button>'
        . '<div class="alert-body">' . $message . '</div>'
        . '</div>';
    }

    protected function getMessenger() {
        return $this->serviceManager->get('messenger');
    }
}
