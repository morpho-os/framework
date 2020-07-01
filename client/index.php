<?php declare(strict_types=1);
if (getenv('MORPHO_DEBUG')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
require __DIR__ . '/../vendor/autoload.php';
\Morpho\App\App::main(new \ArrayObject(require __DIR__ . '/../conf/app.conf.php'));
