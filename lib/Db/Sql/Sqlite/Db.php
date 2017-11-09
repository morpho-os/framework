<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\Sqlite;

use Morpho\Base\NotImplementedException;
use Morpho\Base\OptionRequiredException;
use Morpho\Db\Sql\Db as BaseDb;
use Morpho\Db\Sql\GeneralQuery;
use Morpho\Db\Sql\ReplaceQuery;
use Morpho\Db\Sql\SchemaManager as BaseSchemaManager;

class Db extends BaseDb {
    public function query(): GeneralQuery {
        throw new NotImplementedException();
    }

    public function newReplaceQuery(): ReplaceQuery {
        throw new NotImplementedException();
    }

    public function schemaManager(): BaseSchemaManager {
        return new SchemaManager($this);
    }

    public function insertRows(string $tableName, array $rows): void {
        throw new NotImplementedException();
    }

    protected function newPdo(array $config): \PDO {
        // @TODO: Support of the :memory:
        if (empty($config['filePath'])) {
            throw new OptionRequiredException('filePath');
        }
        $db = new \PDO(self::SQLITE_DRIVER . ':' . $config['filePath']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        if (empty($config['noWal'])) {
            $db->exec('PRAGMA journal_mode=WAL');
        }
        return $db;
    }
}