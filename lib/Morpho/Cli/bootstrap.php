<?php
require_once __DIR__ . '/shell-functions.php';

(new \Morpho\Base\Environment())->init();
(new \Morpho\Error\ErrorHandler())->register();