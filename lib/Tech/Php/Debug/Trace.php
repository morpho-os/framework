<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Tech\Php\Debug;

class Trace {
    protected $frames = [];

    public function __construct() {
        $trace = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $this->frames = [];
        foreach ($trace as $frame) {
            if (isset($frame['file']) && \dirname($frame['file']) == __DIR__) {
                continue;
            }
            $this->frames[] = $this->normalizeFrame($frame);
        }
    }

    public function __toString() {
        $lines = [];
        foreach ($this->frames as $index => $frame) {
            $lines[] = '#' . $index . ' ' . $frame;
        }

        return \implode("\n", $lines);
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
            [
                'function' => $function,
                'filePath' => isset($frame['file']) ? $frame['file'] : null,
                'line'     => isset($frame['line']) ? $frame['line'] : null,
            ]
        );
    }
}
