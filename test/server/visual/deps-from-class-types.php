<?php
namespace Morpho\Test;

use function Morpho\Base\writeLn;
use Morpho\Fs\Directory;

require __DIR__ . '/../../../vendor/autoload.php';

$filesWithSyntaxError = [];
$processed = 0;
foreach (Directory::filePathsWithExt(VENDOR_DIR_PATH, ['php']) as $filePath) {
    try {
        d()->varDump($filePath, \Morpho\Code\ClassTypeDiscoverer::fileDependsFromClassTypes($filePath));
    } catch (\PhpParser\Error $e) {
        writeLn("Failed to handle the file '$filePath'");
        if (0 === stripos($e->getMessage(), 'Syntax error')) {
            $filesWithSyntaxError[] = $filePath;
        } else {
            throw $e;
        }
    } catch (\Throwable $e) {
        writeLn("Failed to handle the file '$filePath'");
        throw $e;
    }
    $processed++;
}
writeLn("Processed: " . $processed);
writeLn("With syntax error: " . count($filesWithSyntaxError));
if (count($filesWithSyntaxError)) {
    d($filesWithSyntaxError);
}
\Morpho\Cli\writeOk();
