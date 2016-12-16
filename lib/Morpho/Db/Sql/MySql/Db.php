<?php
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\ArrayTool;

class Db {
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 3306;
    const DEFAULT_USER = 'root';
    const DEFAULT_PASSWORD = '';
    const DEFAULT_CHARSET = 'utf8';
    const DEFAULT_DB = '';

    public static function connect($options): \PDO {
        $options = ArrayTool::handleOptions($options, [
            'host' => self::DEFAULT_HOST,
            'port' => self::DEFAULT_PORT,
            'user' => self::DEFAULT_USER,
            'db' => self::DEFAULT_DB,
            'password' => self::DEFAULT_PASSWORD,
            'charset' => self::DEFAULT_CHARSET,
            'pdoOptions' => [],
        ]);
        $dsn = \Morpho\Db\Sql\Db::MYSQL_DRIVER . ':dbname=' . $options['db'] . ';' . $options['host'] . ';' . $options['charset'];
        return new \PDO($dsn, $options['user'], $options['password'], $options['pdoOptions']);
    }
}