<?php declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\ArrayTool;
use Morpho\Db\Sql\Db as BaseDb;
use Morpho\Db\Sql\ReplaceQuery;
use Morpho\Db\Sql\Schema as BaseSchema;
use Morpho\Db\Sql\GeneralQuery as BaseGeneralQuery;

class Db extends BaseDb {
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 3306;
    const DEFAULT_USER = 'root';
    const DEFAULT_PASSWORD = '';
    const DEFAULT_CHARSET = 'utf8';
    const DEFAULT_DB = '';

    private $schema;

    private $query;

    public function query(): BaseGeneralQuery {
        if (null === $this->query) {
            $this->query = new GeneralQuery();
        }
        return $this->query;
    }

    public function schema(): BaseSchema {
        if (null === $this->schema) {
            $this->schema = new Schema($this);
        }
        return $this->schema;
    }

    // @TODO: Move to Query
    public function insertRows(string $tableName, array $rows, array $options = null): void {
        $args = [];
        $keys = null;
        foreach ($rows as $row) {
            if (null === $keys) {
                $keys = array_keys($row);
            }
            $args = array_merge($args, array_values($row));
        }
        $query = $this->query();
        $valuesClause = ', (' . implode(', ', $query->positionalPlaceholders($keys)) . ')';
        $sql = 'INSERT INTO ' . $query->quoteIdentifier($tableName) . ' (' . implode(', ', $query->quoteIdentifiers($keys)) . ') VALUES ' . ltrim(str_repeat($valuesClause, count($rows)), ', ');
        $this->eval($sql, $args);
    }

    public function newReplaceQuery(): ReplaceQuery {
        return new ReplaceQuery($this);
    }

    protected function newPdo(array $options): \PDO {
        $options = ArrayTool::handleOptions($options, [
            'host' => self::DEFAULT_HOST,
            'port' => self::DEFAULT_PORT,
            'user' => self::DEFAULT_USER,
            'db' => self::DEFAULT_DB,
            'password' => self::DEFAULT_PASSWORD,
            'charset' => self::DEFAULT_CHARSET,
            'pdoOptions' => [],
        ]);
        $dsn = self::MYSQL_DRIVER . ':dbname=' . $options['db'] . ';' . $options['host'] . ';' . $options['charset'];
        return new \PDO($dsn, $options['user'], $options['password'], $options['pdoOptions']);
    }
}