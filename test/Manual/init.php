<?php declare(strict_types=1);
namespace Morpho\Test\Manual;

error_reporting(E_ALL);
ini_set('display_errors', '1');
set_error_handler(function ($severity, $message, $filePath, $lineNo) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new \ErrorException($message, 0, $severity, $filePath, $lineNo);
});
assert_options(ASSERT_ACTIVE, true);     // enable assert() evaluation?
assert_options(ASSERT_WARNING, false);   // issue a PHP warning for each failed assertion, handled by the ASSERT_CALLBACK
assert_options(ASSERT_BAIL, false);      // terminate execution on failed assertions, handled by the ASSERT_CALLBACK
assert_options(ASSERT_QUIET_EVAL, true); // disable error_reporting during assertion expression evaluation
assert_options(                          // callback to call on failed assertions
    ASSERT_CALLBACK,
    function (string $filePath, int $lineNo, string $assertionExpr, string $description = null) {
        echo 'Failed assertion: ' . rtrim($assertionExpr) . "\n";
        echo 'Description: ' . rtrim($description) . "\n";
        echo "File: $filePath\n";
        echo "Line: $lineNo\n";
        exit(1);
    }
);

require __DIR__ . '/../../vendor/autoload.php';