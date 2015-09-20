<?php
use Morpho\Debug\Debugger;

require_once __DIR__ . '/Trace.php';
require_once __DIR__ . '/Frame.php';
require_once __DIR__ . '/Debugger.php';

/**
 * @param $args
 * @return mixed
 */
function d(...$args) {
    $debugger = Debugger::getInstance();
    return count($args)
        ? $debugger->ignoreCaller(__FILE__, __LINE__)->dump(...$args)
        : $debugger;
}

function dd() {
    exit(Debugger::getInstance()->ignoreCaller(__FILE__)->dump());
}

function dt() {
    exit(Debugger::getInstance()->ignoreCaller(__FILE__)->trace());
}
