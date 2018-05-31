<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
use Morpho\Debug\Debugger;

require_once __DIR__ . '/Trace.php';
require_once __DIR__ . '/Frame.php';
require_once __DIR__ . '/Debugger.php';

if (!function_exists('d')) {
    function d(...$args) {
        $debugger = Debugger::instance();
        return \count($args)
            ? $debugger->ignoreCaller(__FILE__, __LINE__)->dump(...$args)
            : $debugger;
    }
}

if (!function_exists('dd')) {
    function dd(): void {
        Debugger::instance()->ignoreCaller(__FILE__)->dump();
    }
}

if (!function_exists('dt')) {
    function dt(): void {
        Debugger::instance()->ignoreCaller(__FILE__)->trace();
    }
}
