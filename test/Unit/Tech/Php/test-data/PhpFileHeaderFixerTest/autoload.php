<?php declare(strict_types=1);
namespace {

    use Morpho\Tech\Php\Debugger;

    require_once __DIR__ . '/Trace.php';
    require_once __DIR__ . '/Frame.php';
    require_once __DIR__ . '/Debugger.php';

    if (!function_exists('d')) {
        function e29311(...$args) {
            $debugger = Debugger::instance();
            return count($args)
                ? $debugger->ignoreCaller(__FILE__, __LINE__)->dump(...$args)
                : $debugger;
        }
    }
}
namespace Morpho\Test\Unit\Tech\Php\PhpFileHeaderFixerTest {

    /**
     * Foo bar
     */
    function foo() {
    }
}