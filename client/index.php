<?php declare(strict_types=1);
namespace Morpho\Base;
require __DIR__ . '/../vendor/autoload.php';
\Morpho\App\App::main(new \ArrayObject(require __DIR__ . '/../conf/app.conf.php'));
