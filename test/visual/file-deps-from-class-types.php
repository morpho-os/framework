<?php declare(strict_types=1);
namespace MorphoTest\Visual;

use function Morpho\Base\showLn;
use const Morpho\Core\VENDOR_DIR_PATH;
use Morpho\Fs\Directory;

require __DIR__ . '/../../../vendor/autoload.php';

$filesWithSyntaxError = [];
$processed = 0;
foreach (Directory::filePathsWithExt(VENDOR_DIR_PATH, ['php']) as $filePath) {
    try {
        d()->varDump($filePath, \Morpho\Code\ClassTypeDiscoverer::fileDependsFromClassTypes($filePath));
    } catch (\PhpParser\Error $e) {
        showLn("Failed to handle the file '$filePath'");
        if (0 === stripos($e->getMessage(), 'Syntax error')) {
            $filesWithSyntaxError[] = $filePath;
        } else {
            throw $e;
        }
    } catch (\Throwable $e) {
        showLn("Failed to handle the file '$filePath'");
        throw $e;
    }
    $processed++;
}
showLn("Processed: " . $processed);
showLn("With syntax error: " . count($filesWithSyntaxError));
if (count($filesWithSyntaxError)) {
    d($filesWithSyntaxError);
}
\Morpho\Cli\showOk();
