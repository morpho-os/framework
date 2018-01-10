<?php
require __DIR__ . '/../vendor/autoload.php';
\Morpho\Web\App::main(new \ArrayObject(require __DIR__ . '/../config/config.php'));