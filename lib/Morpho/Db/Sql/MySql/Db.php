<?php
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\ArrayTool;

class Db {
    public static function connect($options): \PDO {
        $options = ArrayTool::handleOptions($options, [
            'host' => '127.0.0.1',
            'user' => 'root',
            'db' => '',
            'password' => '',
            'charset' => 'UTF-8',
            'pdoOptions' => [],
        ]);
        $dsn = \Morpho\Db\Sql\Db::MYSQL_DRIVER . ':dbname=' . $options['db'] . ';' . $options['host'] . ';' . $options['charset'];
        return new \PDO($dsn, $options['user'], $options['password'], $options['pdoOptions']);
    }
}