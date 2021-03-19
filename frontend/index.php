<?php declare(strict_types=1);
namespace Morpho\App;
require __DIR__ . '/../vendor/autoload.php';
App::main(require __DIR__ . '/../conf/' . APP_CONF_FILE_NAME);
