<?php
namespace Morpho\Error;

class DumpListener implements IExceptionListener {
    const FAILURE_EXIT_CODE = 1;
    
    public function onException(\Throwable $exception): void {
        $exAsString = $exception->__toString();

        // The d() function can be found in the morpho/debug package.
        if (function_exists('d')) {
            d()->setExitCode(self::FAILURE_EXIT_CODE)
                ->dump($exAsString);
        } else {
            $message = PHP_SAPI == 'cli'
                ? $exAsString
                : '<pre>' . print_r(htmlspecialchars($exAsString, ENT_QUOTES), true) . '</pre>';
            echo $message;
        }
        exit(self::FAILURE_EXIT_CODE);
    }
}
