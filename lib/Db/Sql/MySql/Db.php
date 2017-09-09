<?php
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Db\Sql\MySql;

use Morpho\Base\ArrayTool;
use Morpho\Db\Sql\Db as BaseDb;
use Morpho\Db\Sql\Query as BaseQuery;
use Morpho\Db\Sql\SchemaManager as BaseSchemaManager;

class Db extends BaseDb {
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 3306;
    const DEFAULT_USER = 'root';
    const DEFAULT_PASSWORD = '';
    const DEFAULT_CHARSET = 'utf8';
    const DEFAULT_DB = '';

    private $schemaManager;

    public function newQuery(): BaseQuery {
        return new Query();
    }

    public function schemaManager(): BaseSchemaManager {
        if (null === $this->schemaManager) {
            $this->schemaManager = new SchemaManager($this);
        }
        return $this->schemaManager;
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
        $query = $this->newQuery();
        $valuesClause = ', (' . implode(', ', $query->positionalPlaceholders($keys)) . ')';
        $sql = 'INSERT INTO ' . $query->identifier($tableName) . ' (' . implode(', ', $query->identifiers($keys)) . ') VALUES ' . ltrim(str_repeat($valuesClause, count($rows)), ', ');
        $this->eval($sql, $args);
    }

    protected function newPdoConnection(array $options): \PDO {
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