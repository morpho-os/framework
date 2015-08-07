<?php
namespace Morpho\Debug;

class Trace {
    protected $frames = array();

    public function __construct() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $this->frames = array();
        foreach ($trace as $frame) {
            if (isset($frame['file']) && dirname($frame['file']) == __DIR__) {
                continue;
            }
            $this->frames[] = $this->normalizeFrame($frame);
        }
    }

    public function __toString() {
        $lines = array();
        foreach ($this->frames as $index => $frame) {
            $lines[] = '#' . $index . ' ' . $frame;
        }

        return implode("\n", $lines);
    }

    public function toArray() {
        return $this->frames;
    }

    /**
     * @param array $frame
     *
     * @return Frame
     */
    protected static function normalizeFrame(array $frame) {
        $function = null;
        if (isset($frame['function'])) {
            $function = $frame['function'] . '()';
        }
        if (isset($frame['class']) && isset($frame['type'])) {
            $function = $frame['class'] . $frame['type'] . $function;
        }

        return new Frame(
            array(
                'function' => $function,
                'filePath' => isset($frame['file']) ? $frame['file'] : null,
                'line' => isset($frame['line']) ? $frame['line'] : null
            )
        );
    }
}
