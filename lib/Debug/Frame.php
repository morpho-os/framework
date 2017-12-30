<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Debug;

class Frame implements \ArrayAccess {
    protected $function;

    protected $line;

    protected $filePath;

    public function __construct(array $options) {
        foreach ($options as $name => $value) {
            $this->$name = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    public function offsetGet($offset) {
        return $this->$offset;
    }

    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }

    public function offsetUnset($offset) {
        unset($this->$offset);
    }

    public function __toString() {
        $filePath = isset($this->filePath) ? $this->filePath : 'unknown';
        $line = isset($this->line) ? $this->line : 'unknown';
        $function = isset($this->function) ? $this->function : 'unknown';

        return $function . " called at [$filePath:$line]";
    }
}
