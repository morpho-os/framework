<?php
use Morpho\Debug\Debugger;

require_once __DIR__ . '/Trace.php';
require_once __DIR__ . '/Frame.php';
require_once __DIR__ . '/Debugger.php';

function d(...$args) {
    $debugger = Debugger::instance();
    return count($args)
        ? $debugger->ignoreCaller(__FILE__, __LINE__)->dump(...$args)
        : $debugger;
}

function dd(): void {
    Debugger::instance()->ignoreCaller(__FILE__)->dump();
}

function dt(): void {
    Debugger::instance()->ignoreCaller(__FILE__)->trace();
}