<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\Sqlite;

use Morpho\Base\NotImplementedException;
use Morpho\Db\Sql\DbClient as BaseDbClient;
use Morpho\Db\Sql\GeneralQuery;
use Morpho\Db\Sql\ReplaceQuery;
use Morpho\Db\Sql\Schema as BaseSchema;

class DbClient extends BaseDbClient {
    public function query(): GeneralQuery {
        throw new NotImplementedException();
    }

    public function newReplaceQuery(): ReplaceQuery {
        throw new NotImplementedException();
    }

    public function schema(): BaseSchema {
        return new Schema($this);
    }

    public function insertRows(string $tableName, array $rows): void {
        throw new NotImplementedException();
    }

    protected function newPdo($config, $pdoConfig): \PDO {
        // @TODO: Support the `:memory`:
        if (empty($config['filePath'])) {
            throw new \RuntimeException("The config param 'filePath' is required");
        }
        $db = new \PDO(self::SQLITE_DRIVER . ':' . $config['filePath'], null, null, $pdoConfig);
        if (empty($config['noWal'])) {
            $db->exec('PRAGMA journal_mode=WAL');
        }
        return $db;
    }
}