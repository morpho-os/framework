<?php
require __DIR__ . '/../vendor/autoload.php';
\Morpho\App\Web\App::main(new \ArrayObject(require __DIR__ . '/../config/app.config.php'));
