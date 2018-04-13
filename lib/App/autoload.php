<?php declare(strict_types=1);
namespace Morpho\App;

require __DIR__ . '/Core/autoload.php';
//if (PHP_SAPI == 'cli') {
    require __DIR__ . '/Cli/autoload.php';
//} else {
    require __DIR__ . '/Web/autoload.php';
//}
