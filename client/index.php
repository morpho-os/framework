<?php declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
\Morpho\App\App::main(new \ArrayObject(require __DIR__ . '/../server/localhost/config/app.config.php'));
