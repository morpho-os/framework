<?php
namespace Morpho\Web;

use Morpho\Base\ItemNotSetException;

class Session implements \Countable, \Iterator, \ArrayAccess {
    protected $name;

    const KEY = 'morpho:http:session:ns';

    protected $data;

    public function __construct(string $name, bool $autoStart = true, bool $forceClear = false) {
        $this->name = $name;

        $this->init($autoStart, $forceClear);
    }

    /**
     * Returns current session name.
     */
    public function getName() {
        return $this->name;
    }

    public function &__get($name) {
        if (array_key_exists($name, $_SESSION[self::KEY][$this->name])) {
            return $_SESSION[self::KEY][$this->name][$name];
        }
        throw new ItemNotSetException($name);
    }

    public function __set($name, $value) {
        $_SESSION[self::KEY][$this->name][$name] = $value;

        return $this;
    }

    public function __isset($name) {
        return isset($_SESSION[self::KEY][$this->name][$name]);
    }

    public function __unset($name) {
        unset($_SESSION[self::KEY][$this->name][$name]);
    }

    public function count() {
        return count($_SESSION[self::KEY][$this->name]);
    }

    public function current() {
        return current($_SESSION[self::KEY][$this->name]);
    }

    /**
     * @return scalar
     */
    public function key() {
        return key($_SESSION[self::KEY][$this->name]);
    }

    /**
     * @return void
     */
    public function next() {
        next($_SESSION[self::KEY][$this->name]);
    }

    /**
     * @return void
     */
    public function rewind() {
        reset($_SESSION[self::KEY][$this->name]);
    }

    /**
     * @return bool
     */
    public function valid() {
        return false !== current($_SESSION[self::KEY][$this->name]);
    }

    public function fromArray(array $data) {
        $_SESSION[self::KEY][$this->name] = array_merge(
            $_SESSION[self::KEY][$this->name],
            $data
        );
    }

    public function toArray(): array {
        return $_SESSION[self::KEY][$this->name];
    }

    public function clear() {
        $_SESSION[self::KEY][$this->name] = [];
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

    protected function init(bool $autoStart, bool $forceClear) {
        if ($autoStart && !isset($_SESSION)) {
            if (headers_sent($filePath, $lineNumber)) {
                throw new \RuntimeException(
                    "Unable to start session, headers were already sent at '$filePath:$lineNumber'."
                );
            }
            session_start();
        }

        if (!isset($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
        if (!isset($_SESSION[self::KEY][$this->name])) {
            $_SESSION[self::KEY][$this->name] = [];
        } elseif ($forceClear) {
            $this->clear();
        }
    }
}
