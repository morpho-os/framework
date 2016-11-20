<?php
(function ($host, $user, $password, $dbName) {
    $dsn = 'mysql:dbname=' . $dbName . ';' . $host . ';charset=UTF8';
    $db = new \PDO($dsn, $user, $password);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    foreach ($db->query('SHOW TABLES') as $tableName) {
        echo array_shift($tableName) . "\n";
    }
    /*
    $sqls = [
        'DROP DATABASE IF EXISTS test; CREATE DATABASE test DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci',
        "GRANT ALL PRIVILEGES ON test.* TO 'root'@'127.0.0.1' IDENTIFIED BY ''",
        "FLUSH PRIVILEGES",
    ];
    */
})('127.0.0.1', 'root', '', 'test');