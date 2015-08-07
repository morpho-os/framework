<?php
namespace Morpho\Fs;

class NatOrderIterator implements \RecursiveIterator {
    const CURRENT_AS_FILEINFO = 0x01;
    const SKIP_DIRS = 0x02;

    protected $index = 0;

    protected $dirPath;

    protected $flags;

    protected $initialized = false;

    protected $items = array();

    public function __construct($dirPath, $flags = null) {
        $this->dirPath = $dirPath;
        if (null === $flags) {
            $flags = self::CURRENT_AS_FILEINFO | self::SKIP_DIRS;
        }
        $this->flags = $flags;
    }

    public function hasChildren() {
        return isset($this->items[$this->index]) && is_dir($this->items[$this->index]);
    }

    public function getChildren() {
        $this->ensureInitialized();

        if (!isset($this->items[$this->index])) {
            throw new \OutOfBoundsException();
        }
        $path = $this->items[$this->index];
        if (!is_dir($path)) {
            throw new \LogicException();
        }
        return new static($path);
    }

    public function key() {
        $this->ensureInitialized();

        return $this->index;
    }

    public function rewind() {
        $this->init();
        $this->index = 0;
    }

    public function valid() {
        return isset($this->items[$this->index]);
    }

    public function current() {
        $this->ensureInitialized();

        if (!isset($this->items[$this->index])) {
            throw new \OutOfBoundsException();
        }
        if ($this->flags & self::CURRENT_AS_FILEINFO) {
            return new \SplFileInfo($this->items[$this->index]);
        }
        return $this->items[$this->index];
    }

    public function next() {
        $this->ensureInitialized();
        $this->index++;
    }

    public function toArray() {
        return iterator_to_array($this);
    }

    protected function init() {
        if ($this->initialized) {
            return;
        }

        $dirPath = $this->dirPath;

        if (!is_dir($dirPath) || empty($dirPath)) {
            throw new IoException("The '$dirPath' directory does not exist.");
        }

        $items = array();
        foreach (glob($dirPath . '/*') as $filePath) {
            if (($this->flags & self::SKIP_DIRS) && is_dir($filePath)) {
                continue;
            }
            $fileName = basename($filePath);
            $items[$filePath] = Path::normalize($fileName);
        }
        natsort($items);

        $this->items = array_keys($items);

        $this->initialized = true;
    }

    protected function ensureInitialized() {
        if (!$this->initialized) {
            $this->init();
        }
    }
}
