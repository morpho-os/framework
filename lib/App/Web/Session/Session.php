<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\App\Web\Session;

class Session implements \Countable, \Iterator, \ArrayAccess {
    protected $storageKey;

    const KEY = __CLASS__;

    protected $data;

    public function __construct(string $storageKey, bool $start = true) {
        $this->storageKey = $storageKey;
        $this->init($start);
    }

    public static function started(): bool {
        return \session_status() == PHP_SESSION_ACTIVE;
        // return defined('SID') || isset($_SESSION);
    }

    public static function start(): void {
        if (self::started()) {
            return;
        }
        \session_start();
    }

    public function storageKey(): string {
        return $this->storageKey;
    }

    public function &__get($name) {
        if (\array_key_exists($name, $_SESSION[self::KEY][$this->storageKey])) {
            return $_SESSION[self::KEY][$this->storageKey][$name];
        }
        throw new \RuntimeException('The specified key has not been set');
    }

    public function __set($name, $value): void {
        $_SESSION[self::KEY][$this->storageKey][$name] = $value;
    }

    public function __isset($name): bool {
        return isset($_SESSION[self::KEY][$this->storageKey][$name]);
    }

    public function __unset($name): void {
        unset($_SESSION[self::KEY][$this->storageKey][$name]);
    }

    public function count(): int {
        return \count($_SESSION[self::KEY][$this->storageKey]);
    }

    public function current() {
        return \current($_SESSION[self::KEY][$this->storageKey]);
    }

    /**
     * @return string|int
     */
    public function key() {
        return \key($_SESSION[self::KEY][$this->storageKey]);
    }

    public function next(): void {
        \next($_SESSION[self::KEY][$this->storageKey]);
    }

    public function rewind(): void {
        \reset($_SESSION[self::KEY][$this->storageKey]);
    }

    public function valid(): bool {
        return false !== \current($_SESSION[self::KEY][$this->storageKey]);
    }

    public function fromArray(array $data): void {
        $_SESSION[self::KEY][$this->storageKey] = \array_merge(
            $_SESSION[self::KEY][$this->storageKey],
            $data
        );
    }

    public function toArr(): array {
        return $_SESSION[self::KEY][$this->storageKey];
    }

    public function clear(): void {
        $_SESSION[self::KEY][$this->storageKey] = [];
    }

    public function offsetExists($key): bool {
        return $this->__isset($key);
    }

    public function &offsetGet($key) {
        return $this->__get($key);
    }

    public function offsetSet($key, $value): void {
        $this->__set($key, $value);
    }

    public function offsetUnset($key): void {
        $this->__unset($key);
    }

    protected function init(bool $start): void {
        if ($start) {
            self::start();
        }

        if (!isset($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
        if (!isset($_SESSION[self::KEY][$this->storageKey])) {
            $_SESSION[self::KEY][$this->storageKey] = [];
        }
    }
}
