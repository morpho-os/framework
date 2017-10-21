<?php
require __DIR__ . '/../vendor/autoload.php';
\Morpho\Web\Application::main(new \ArrayObject(require __DIR__ . '/../config/config.php'));