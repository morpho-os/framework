<?php
namespace Morpho\Db\Sql\Sqlite;

use Morpho\Base\NotImplementedException;
use Morpho\Base\OptionRequiredException;
use Morpho\Db\Sql\Db as BaseDb;
use Morpho\Db\Sql\Query as BaseQuery;
use Morpho\Db\Sql\SchemaManager as BaseSchemaManager;

class Db extends BaseDb {
    public function query(): BaseQuery {
        return new Query();
    }

    public function schemaManager(): BaseSchemaManager {
        return new SchemaManager($this);
    }

    public function insertRows(string $tableName, array $rows/* @TODO:, int $rowsInBlock = 100 */): void {
        throw new NotImplementedException();
    }

    protected function newPdoConnection(array $options): \PDO {
        // @TODO: Support of the :memory:
        if (empty($options['filePath'])) {
            throw new OptionRequiredException('filePath');
        }
        $db = new \PDO(self::SQLITE_DRIVER . ':' . $options['filePath']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        if (empty($options['noWal'])) {
            $db->exec('PRAGMA journal_mode=WAL');
        }
        return $db;
    }
}