<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Messages;

/**
 * @TODO: Implement \IteratorAggregate
 */
class Messenger implements \Countable {
    public const ERROR   = 'error';
    public const INFO    = 'info';
    public const SUCCESS = 'success';
    public const WARNING = 'warning';

    /**
     * @var IMessageStorage
     */
    protected $messages;

    protected $allowedTypes = [
        self::SUCCESS,
        self::INFO,
        self::WARNING,
        self::ERROR,
    ];

    public function clearMessages(): void {
        $this->initMessageStorage();
        $this->messages->clear();
    }

    public function addSuccessMessage(string $text, array $args = null): void {
        $this->addMessage($text, $args, self::SUCCESS);
    }

    public function addInfoMessage(string $text, array $args = null): void {
        $this->addMessage($text, $args, self::INFO);
    }

    public function addWarningMessage(string $text, array $args = null): void {
        $this->addMessage($text, $args, self::WARNING);
    }

    public function addErrorMessage(string $text, array $args = null): void {
        $this->addMessage($text, $args, self::ERROR);
    }

    public function hasWarningMessages(): bool {
        return isset($this->messages[self::WARNING]) && count($this->messages[self::WARNING]) > 0;
    }

    public function hasErrorMessages(): bool {
        return isset($this->messages[self::ERROR]) && count($this->messages[self::ERROR]) > 0;
    }

    public function addMessage(string $text, array $args = null, $type = null): void {
        if (null === $type) {
            $type = self::SUCCESS;
        }
        $this->checkMessageType($type);
        $this->initMessageStorage();
        if (!isset($this->messages[$type])) {
            $this->messages[$type] = [];
        }
        $this->messages[$type][] = [
            'text' => $text,
            'args' => (array)$args,
        ];
    }

    public function toArray(): array {
        $this->initMessageStorage();
        return $this->messages->toArray();
    }

    public function count(): int {
        $this->initMessageStorage();
        return count($this->messages);
    }

    public function setMessageStorage(IMessageStorage $storage): void {
        $this->messages = $storage;
    }

    protected function initMessageStorage(): void {
        if (null === $this->messages) {
            $this->messages = $this->newMessageStorage();
        }
    }

    protected function newMessageStorage(): IMessageStorage {
        return new SessionMessageStorage(__CLASS__);
    }

    protected function checkMessageType($type): void {
        if (!in_array($type, $this->allowedTypes)) {
            throw new \UnexpectedValueException();
        }
    }
}
