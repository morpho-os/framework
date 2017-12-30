<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Error;

class DumpListener {
    const FAILURE_EXIT_CODE = 1;

    /**
     * @param \Throwable $exception
     */
    public function __invoke($exception): void {
        $exAsString = $exception->__toString();
        // The d() function can be found in the morpho/debug package.
        if (function_exists('d')) {
            d()->dumpWithExitCode($exAsString, self::FAILURE_EXIT_CODE);
        } else {
            $message = PHP_SAPI == 'cli'
                ? $exAsString
                : '<pre>' . print_r(htmlspecialchars($exAsString, ENT_QUOTES), true) . '</pre>';
            echo $message;
        }
        exit(self::FAILURE_EXIT_CODE);
    }
}
