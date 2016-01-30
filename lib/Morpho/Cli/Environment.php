<?php
namespace Morpho\Cli;

use Morpho\Base\Environment as BaseEnvironment;

class Environment extends BaseEnvironment {
    const SUCCESS_EXIT_CODE = 0;
    const ERROR_EXIT_CODE = 1;

    public function initServerVars(array $serverVars = []) {
        $defaultServerVars = [
            'HTTP_HOST'       => 'localhost',
            'SCRIPT_NAME'     => null,
            'REMOTE_ADDR'     => '127.0.0.1',
            'REQUEST_METHOD'  => 'GET',
            'SERVER_NAME'     => null,
            'SERVER_SOFTWARE' => null,
            'HTTP_USER_AGENT' => null,
            'SERVER_PROTOCOL' => 'HTTP/1.0',
            'REQUEST_URI'     => '',
        ];
        $_SERVER += $serverVars + $defaultServerVars;
    }
}