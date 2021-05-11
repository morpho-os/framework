#!/usr/bin/env php
<?php
namespace Morpho\Infra;

require __DIR__ . '/../vendor/autoload.php';

function main7c897(): void {
//    //passthru('cd ' . escapeshellarg($baseDirPath) . ' && ' . escapeshellarg($baseDirPath) . '/vendor/bin/psalm', $exitCode);
//    //$ok = $ok && ($exitCode === 0);
    $ok = true;

    exit($ok ? 0 : 1);
}

main();