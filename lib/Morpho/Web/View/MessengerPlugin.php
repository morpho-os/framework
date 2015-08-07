<?php
namespace Morpho\Web\View;

class MessengerPlugin implements \Countable {
    const SUCCESS = 'success';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';

    protected $messages;

    protected $allowedTypes = [
        self::SUCCESS,
        self::INFO,
        self::WARNING,
        self::ERROR,
    ];

    public function clearMessages() {
        $this->initMessageStorage();
        $this->messages->clear();
    }

    public function addSuccessMessage($message, ...$args) {
        $this->addMessage($message, $args, self::SUCCESS);
    }

    public function addInfoMessage($message, ...$args) {
        $this->addMessage($message, $args, self::INFO);
    }

    public function addWarningMessage($message, ...$args) {
        $this->addMessage($message, $args, self::WARNING);
    }

    public function hasWarningMessages() {
        return isset($this->messages[self::WARNING])
        && count($this->messages[self::WARNING]) > 0;
    }

    public function addErrorMessage($message, ...$args) {
        $this->addMessage($message, $args, self::ERROR);
    }

    public function hasErrorMessages() {
        return isset($this->messages[self::ERROR]) && count($this->messages[self::ERROR]) > 0;
    }

    public function addMessage($message, array $args = null, $type = null) {
        if (null === $type) {
            $type = self::SUCCESS;
        }
        $this->checkMessageType($type);
        $this->initMessageStorage();
        if (!isset($this->messages[$type])) {
            $this->messages[$type] = [];
        }
        $this->messages[$type][] = [
            'message' => $message,
            'args' => (array)$args,
        ];
    }

    public function toArray() {
        $this->initMessageStorage();
        return $this->messages->toArray();
    }

    public function count() {
        $this->initMessageStorage();
        return count($this->messages);
    }

    public function __invoke() {
        return $this;
    }

    public function setMessageStorage(IMessageStorage $storage) {
        $this->messages = $storage;
    }

    public function renderPageMessages() {
        $output = '';
        if ($this->count()) {
            $renderedMessages = [];
            foreach ($this->messages as $type => $messages) {
                $renderedMessages[] = $this->renderMessages($messages, $type);
            }
            $output = $this->wrapPageMessages(implode("\n", $renderedMessages));
        }
        $this->messages->clear();
        return $output;
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
        $replacePairs = [];
        foreach ($message['args'] as $key => $value) {
            $replacePairs['{' . $key . '}'] = nl2br(escapeHtml($value));
        }
        return $this->wrapMessage(
            strtr($message['message'], $replacePairs),
            $type
        );
    }

    protected function wrapMessage($message, $type) {
        return '<div class="alert alert-' . dasherize($type) . '">'
        . '<button type="button" class="close" data-dismiss="alert">&times;</button>'
        . '<div class="alert-body">' . $message . '</div>'
        . '</div>';
    }

    protected function initMessageStorage() {
        if (null === $this->messages) {
            $this->messages = $this->createMessageStorage();
        }
    }

    protected function createMessageStorage() {
        return new SessionMessageStorage(__CLASS__);
    }

    protected function checkMessageType($type) {
        if (!in_array($type, $this->allowedTypes)) {
            throw new \UnexpectedValueException();
        }
    }
}
