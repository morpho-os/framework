<?php
require_once __DIR__ . '/autoload.php';

(new \Morpho\Cli\Environment())->init();
(new \Morpho\Error\ErrorHandler())->register();