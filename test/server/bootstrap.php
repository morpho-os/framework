<?php
date_default_timezone_set('UTC');

/*if (getenv('TRAVIS')) {
    (function () {
        $dsn = 'mysql:dbname=;127.0.0.1;charset=UTF8';
        $db = new \PDO($dsn, 'root', '');
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $sqls = [
            'DROP DATABASE IF EXISTS test; CREATE DATABASE test DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci',
            "GRANT ALL PRIVILEGES ON test.* TO 'root'@'127.0.0.1' IDENTIFIED BY ''",
            "FLUSH PRIVILEGES",
        ];
        foreach ($sqls as $sql) {
            $db->query($sql);
        }
    })();
}*/

(function () {
    $classLoader = require __DIR__ . '/../../vendor/autoload.php';
    $classLoader->add('MorphoTest', __DIR__ . '/unit');
    foreach (glob(MODULE_DIR_PATH . '/*') as $moduleDirPath) {
        $autoloadFilePath = $moduleDirPath . '/' . VENDOR_DIR_NAME . '/' . AUTOLOAD_FILE_NAME;
        if (is_file($autoloadFilePath)) {
            require $autoloadFilePath;
        }
    }
})();
