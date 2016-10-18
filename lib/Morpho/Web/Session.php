<?php
namespace Morpho\Web;

use Morpho\Base\ItemNotSetException;

class Session implements \Countable, \Iterator, \ArrayAccess {
    protected $storageKey;

    const KEY = __CLASS__;

    protected $data;

    public function __construct(string $storageKey, bool $start = true) {
        $this->storageKey = $storageKey;
        $this->init($start);
    }

    public static function started(): bool {
        return session_status() == PHP_SESSION_ACTIVE;
        // return defined('SID') || isset($_SESSION);
    }

    public static function start() {
        if (self::started()) {
            return;
        }
        session_start();
    }

    public function storageKey(): string {
        return $this->storageKey;
    }

    public function &__get($name) {
        if (array_key_exists($name, $_SESSION[self::KEY][$this->storageKey])) {
            return $_SESSION[self::KEY][$this->storageKey][$name];
        }
        throw new ItemNotSetException($name);
    }

    public function __set($name, $value) {
        $_SESSION[self::KEY][$this->storageKey][$name] = $value;

        return $this;
    }

    public function __isset($name) {
        return isset($_SESSION[self::KEY][$this->storageKey][$name]);
    }

    public function __unset($name) {
        unset($_SESSION[self::KEY][$this->storageKey][$name]);
    }

    public function count() {
        return count($_SESSION[self::KEY][$this->storageKey]);
    }

    public function current() {
        return current($_SESSION[self::KEY][$this->storageKey]);
    }

    /**
     * @return scalar
     */
    public function key() {
        return key($_SESSION[self::KEY][$this->storageKey]);
    }

    /**
     * @return void
     */
    public function next() {
        next($_SESSION[self::KEY][$this->storageKey]);
    }

    /**
     * @return void
     */
    public function rewind() {
        reset($_SESSION[self::KEY][$this->storageKey]);
    }

    /**
     * @return bool
     */
    public function valid() {
        return false !== current($_SESSION[self::KEY][$this->storageKey]);
    }

    public function fromArray(array $data) {
        $_SESSION[self::KEY][$this->storageKey] = array_merge(
            $_SESSION[self::KEY][$this->storageKey],
            $data
        );
    }

    public function toArray(): array {
        return $_SESSION[self::KEY][$this->storageKey];
    }

    public function clear() {
        $_SESSION[self::KEY][$this->storageKey] = [];
    }

    public function offsetExists($key) {
        return $this->__isset($key);
    }

    public function &offsetGet($key) {
        return $this->__get($key);
    }

    public function offsetSet($key, $value) {
        $this->__set($key, $value);
    }

    public function offsetUnset($key) {
        $this->__unset($key);
    }

    protected function init(bool $start) {
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
