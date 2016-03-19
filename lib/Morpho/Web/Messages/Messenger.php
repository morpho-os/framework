<?php
namespace Morpho\Web\Messages;

class Messenger implements \Countable {
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

    public function addSuccessMessage($message, array $args = null) {
        $this->addMessage($message, $args, self::SUCCESS);
    }

    public function addInfoMessage($message, array $args = null) {
        $this->addMessage($message, $args, self::INFO);
    }

    public function addWarningMessage($message, array $args = null) {
        $this->addMessage($message, $args, self::WARNING);
    }

    public function addErrorMessage($message, array $args = null) {
        $this->addMessage($message, $args, self::ERROR);
    }

    public function hasWarningMessages() {
        return isset($this->messages[self::WARNING])
        && count($this->messages[self::WARNING]) > 0;
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
            'args'    => (array)$args,
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

    public function setMessageStorage(IMessageStorage $storage) {
        $this->messages = $storage;
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
