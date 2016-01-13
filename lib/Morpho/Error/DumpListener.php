<?php
namespace Morpho\Error;

class DumpListener implements IExceptionListener {
    public function onException(\Throwable $exception) {
        $exAsString = $exception->__toString();

        // The d() function can be found in the morpho/debug package.
        if (function_exists('d')) {
             d()->dump($exAsString);

            exit();
        }

        $message = PHP_SAPI == 'cli'
            ? $exAsString
            : '<pre>' . print_r(htmlspecialchars($exAsString, ENT_QUOTES), true) . '</pre>';
        exit($message);
    }
}
