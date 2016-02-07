<?php
require_once __DIR__ . '/functions.php';

(new \Morpho\Cli\Environment())->init();
(new \Morpho\Error\ErrorHandler())->register();