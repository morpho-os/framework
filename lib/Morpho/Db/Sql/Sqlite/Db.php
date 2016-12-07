<?php
namespace Morpho\Db\Sql\Sqlite;

use Morpho\Base\OptionRequiredException;

class Db {
    public static function connect($options): \PDO {
        if (empty($options['filePath'])) {
            throw new OptionRequiredException('filePath');
        }
        $db = new \PDO(\Morpho\Db\Sql\Db::SQLITE_DRIVER . ':' . $options['filePath']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        if (empty($options['noWal'])) {
            $db->exec('PRAGMA journal_mode=WAL');
        }
        return $db;
    }
}