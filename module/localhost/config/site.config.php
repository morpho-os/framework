<?php
use const Morpho\App\VENDOR;
use const Morpho\App\CACHE_DIR_NAME;

$moduleDirPath = \dirname(__DIR__);
return [
    'path' => [
        'cacheDirPath' => $moduleDirPath . '/' . CACHE_DIR_NAME,
    ],
    'module' => [
        VENDOR . '/system',
//        VENDOR . '/user',
    ],
    'service' => [
        'router' => [
            'home' => [
                'handler' => [VENDOR . '/system', 'Index', 'index'],
                'method' => 'get',
            ],
        ],
        'db' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'user' => 'root',
            'password' => '',
            'db' => '',
            'port' => '3306',
        ],
        'moduleAutoloader' => [
            'useCache' => false,
        ],
        'templateEngine' => [
            'useCache' => false,
/*            'forceCompileTs' => false,
            'nodeBinDirPath' => getenv('NODE_BIN_DIR_PATH') ?: '/usr/bin',
            'tsOptions' => [
                '--forceConsistentCasingInFileNames',
                '--removeComments',
                '--noImplicitAny',
                '--suppressImplicitAnyIndexErrors',
                '--noEmitOnError',
                '--newLine LF',
                '--allowJs',
            ],*/
        ],
        'errorHandler' => [
            'dumpListener' => true,
            'noDupsListener' => false,
        ],
        'dispatchErrorHandler' => [
            'throwErrors' => false,
            //'exceptionHandler' => ['my-vendor/my-module', 'MyError', 'uncaught']
        ],
        'errorLogger' => [
            'mailWriter' => [
                'enabled' => false,
                'mailFrom' => 'admin@localhost',
                'mailTo' => 'admin@localhost',
            ],
            'logFileWriter' => true,
            'debugWriter' => true,
            'errorLogWriter' => false,
        ],
        'view' => [
            'pageRenderer' => VENDOR . '/system',
        ],
    ],
    'umask' => 0007, // This is valid for the `development` environment, change it for other environments.
    'iniConfig' => [
        //'display_errors' => '1',
        //'date.timezone' => 'UTC',
        //'default_charset' => 'UTF-8',
        'session' => [
            // The commented out settings contain default values from the PHP manual: https://php.net/manual/en/session.configuration.php. The session.upload* settings have not been included here. Settings without comments are fixes for those settings for which it makes sense.
            /*
            // Type: string
            'cookie_domain' => '',
            // Type: int
            'cookie_lifetime' => '0',
            // Type: string
            'cookie_path' => '/',
            // Type: bool
            'cookie_secure' => '0',
            // Type: bool
            'cookie_httponly' => '0',
            // Type: bool
            'use_cookies' => '1',
            // Type: bool
            'use_only_cookies' => '1',
            */

            // Type: bool
            'use_strict_mode' => '1',
            /*
            // Type: int
            'gc_divisor' => '1',
            // Type: int
            'gc_probability' => '100',
            // Type: ??
            'gc_maxlifetime' => '1440',

            // Type: bool
            'lazy_write' => '1',
            */
            // Type: string
            'name' => 's',
            /*
            // Type: string
            'referer_check' => '',
            // Type: string, possible values: "nocache" (default) | "private" | "private_no_expire" | "public"
            'cache_limiter' => "nocache",
            // Type: int
            'cache_expire' => '180',
            // Type: string
            'save_handler' => 'files',
            // Type: string
            'save_path' => '',
            // Type: bool
            'use_trans_sid' => '0',
            // Type: string
            'trans_sid_tags' => "a=href,area=href,frame=src,form=",
            // Type: string
            'trans_sid_hosts' => $_SERVER['HTTP_HOST'],
            */
            // Type: string, possible values: "php" (default) | "php_serialize" | "php_binary" | "wddx"
            'serialize_handler' => 'php_serialize',
            /*
            // Type: int
            'sid_bits_per_character' => '5',
            // Type: int
            'sid_length' => '32',
            */
        ],
    ],
];
