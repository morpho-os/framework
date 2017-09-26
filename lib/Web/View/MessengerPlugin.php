<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web\View;

use function Morpho\Base\{
    dasherize, escapeHtml, filterStringArgs
};
use Morpho\Di\{
    IServiceManager, IWithServiceManager
};

class MessengerPlugin extends Plugin implements \Countable, IWithServiceManager {
    private $serviceManager;

    public function count() {
        return $this->messenger()->count();
    }

    public function renderPageMessages() {
        $html = '';
        $messenger = $this->messenger();
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
        $text = filterStringArgs(
            nl2br(escapeHtml($message['text'])),
            $message['args'],
            function ($arg) { return $arg; }
        );
        return $this->wrapMessage($text, $type);
    }

    protected function wrapMessage($message, $type) {
        return '<div class="alert alert-' . dasherize($type) . '">'
        . '<button type="button" class="close" data-dismiss="alert">&times;</button>'
        . '<div class="alert-body">' . $message . '</div>'
        . '</div>';
    }

    protected function messenger() {
        return $this->serviceManager->get('messenger');
    }
}
